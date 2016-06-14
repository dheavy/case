"""CASE (MyPleasure API) logger."""
# -*- coding: utf-8 -*-$
import os
import logging
import logging.handlers
import smtplib
import slacker
import requests
import urllib.parse
from socket import gaierror
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.header import Header
from email.utils import formataddr


class Mann(object):
    """
    Mann is a logger and notifier util.

    Named after Dr. Mann from Interstellar,
    it logs message and errors, and even send them away
    as Slack messages, Trello tasks and emails.

    It was created to notify quickly of possible problems
    occuring on side project apps such as MyPleasure (https://mypleasu.re).
    """

    def __init__(self, *args, **kwargs):
        """
        Mann is a logger and notifier util.

        It both logs to console and files, and can notify
        these logs via emails, Slack and Trello.

        Instantiate it passing booleans in *args specifying
        the logger and notifiers to use.
        Use the `log` method to log/notify.

        Default were set to simplify configuration.
        i.e. File logging uses RotatingFileHandler with 'ab' write mode,
        max bytes size of 2000, a backup count of 100 and UTF-8 encoding.

        console - print in console if exists
        file    - logs to file if exists
        email   - send log via email if exists
        slack   - sends Slack message if exists
        trello  - create Trello task based on log, if exists

        **kwargs:
            {
                'console': True,
                'file': {
                    'info': <info-outfile-handle>,
                    'error': <error-outfile-handle>
                },
                'email': {
                    'server': <smtp-server>,
                    'port': <smtp-port-defaults-to-587>,
                    'sendername': <human-friendly-sender-name>,
                    'from': <email-from-address>,
                    'to': <email-to-address>,
                    'subject': <subject-line>,
                    'user': <smtp-user>,
                    'password': <smtp-password>
                },
                'slack': {
                    'key': <api-key>,
                    'channel': <api-channel>,
                    'username': <bot-name>
                },
                'trello': {
                    'key': <api-key>,
                    'token': <oauth-token>,
                    'list': '<list-id>',
                    'cardname': <optional-card-name>
                    'members': <string-id-or-tuple-of-member-ids-to-add>
                }
            }
        """
        self.config = kwargs
        self.has_enabled_console = bool(self.config.get('console', None))
        self.has_enabled_file = bool(self.config.get('file', None))
        self.has_enabled_email = bool(self.config.get('email', None))
        self.has_enabled_slack = bool(self.config.get('slack', None))
        self.has_enabled_trello = bool(self.config.get('trello', None))
        self.should_raise_exception = bool(self.config.get(
            'raise_exception', None
        ))

    def log(self, msg='', error=False):
        """
        Log file into desired outputs.

        Args:
            msg:   A serialized message to output.
            error: Used to branch out output types
                   for services aware of it (i.e. file).
        """
        def unit(value, use):
            return (use is not False and value is not None) and value or None

        def bind(v, f, err=False):
            return bool(v) is not False and f(v, err) or None

        bind(unit(msg, self.has_enabled_console), self.console, err=error)
        bind(unit(msg, self.has_enabled_file), self.file, err=error)
        bind(unit(msg, self.has_enabled_email), self.email, err=error)
        bind(unit(msg, self.has_enabled_slack), self.slack, err=error)
        bind(unit(msg, self.has_enabled_trello), self.trello, err=error)

    def console(self, msg, error=False):
        """Print message in console."""
        output = ''
        if error is True:
            output += '[ERROR] '
        output += msg

        print(output)

    def file(self, msg, error=False):
        """Log message to file."""
        try:
            self.__set_file_logger()
        except Exception:
            self.console(msg)
            return

        try:
            if error is False:
                self.info_log.info(msg)
            else:
                self.error_log.error(msg)
        except Exception as e:
            self.console(e, error=True)

    def email(self, msg, error=False):
        """Email message."""
        self.__set_email_logger()

        mail = MIMEMultipart()
        mail['Subject'] = ''
        if error is True:
            mail['Subject'] += '[ERROR] '
            mail['Subject'] += self.config.get('email', {}).get('subject', '')
        mail['From'] = formataddr((str(
            Header(self.config.get(
                'email', {}).get('sendername', ''), 'utf-8'
            )),
            self.config.get('email', {}).get('from')
        ))
        mail['To'] = self.config.get('email', {}).get('to', '')
        mail.attach(MIMEText(msg, 'plain'))
        try:
            self.mailer.starttls()
            self.mailer.login(
                self.config.get('email').get('user', ''),
                self.config.get('email', {}).get('password', '')
            )
            self.mailer.sendmail(
                self.config.get('email', {}).get('from'),
                [self.config.get('email', {}).get('to')],
                msg
            )
            self.mailer.quit()
        except smtplib.SMTPDataError as e:
            self.file(e, error=True)

    def slack(self, msg, error=False):
        """Send as Slack message."""
        self.__set_slack_logger()

        try:
            channel = self.config.get('slack', {}).get('channel', '#random')
            botname = self.config.get('slack', {}).get('username', '')
            self.slacker.chat.post_message(channel, msg, username=botname)
        except Exception as e:
            self.file(e, error=True)

    def trello(self, msg, error=False):
        """Turn message to Trello card."""
        if error is True:
            msg = '[ERROR] ' + msg

        try:
            trello_conf = self.config.get('trello', {})
            payload = {
                'key': trello_conf.get('key', ''),
                'token': trello_conf.get('token', ''),
                'idList': trello_conf.get('list', ''),
                'name': trello_conf.get('cardname', msg),
                'desc': msg,
                'pos': 'top',
                'members': trello_conf.get('members', None)
            }
            encoded = urllib.parse.urlencode(payload)
            uri = 'https://api.trello.com/1/cards?{0}'.format(encoded)

            req = requests.post(uri)
            json_card = req.json()

            # If members where given, dispatch them on card.
            # TODO: Refactor plumbing down there with Monads...
            if payload['members'] is not None:
                members = None
                if isinstance(payload['members'], str):
                    members = (payload['members'],)
                elif isinstance(payload['members'], tuple):
                    members = payload['members']

                if members is not None:
                    key = payload['key']
                    token = payload['token']
                    cid = json_card['id']

                    for m in members:
                        encoded = {'key': key, 'token': token, 'value': m}
                        encoded = urllib.parse.urlencode(encoded)

                        base = 'https://api.trello.com/1/cards/'
                        base += cid + '/idMembers?'

                        uri = '{0}{1}'.format(base, encoded)
                        req = requests.post(uri)

        except Exception as e:
            self.file(e, error=True)

    def __set_file_logger(self):
        """Prepare file loggers."""
        if not hasattr(self, 'info_log') or not hasattr(self, 'error_log'):
            self.__fh_info = None
            self.__fh_error = None
            self.info_log = logging.getLogger(__name__ + '.info')
            self.error_log = logging.getLogger(__name__ + '.error')

            def prepare_handler(logger, prop, key, level):
                fmt = logging.Formatter(
                    '%(asctime)s - %(levelname)s - %(message)s'
                )
                if not hasattr(self, prop):
                    f = self.config.get('file', {}).get(key, None)

                    # Create file loggers if filename were given,
                    # otherwise bail out with exception.
                    # It will be caught up to ensure error is
                    # instead printed in console.
                    if f is not None:
                        handler = logging.handlers.RotatingFileHandler(
                            f, mode='ab', maxBytes=2000,
                            backupCount=100, encoding='utf-8'
                        )
                        setattr(self, prop, handler)
                        getattr(self, prop).setFormatter(fmt)
                        logger.setLevel(level)
                        logger.addHandler(handler)
                    else:
                        raise Exception()

            prepare_handler(self.info_log, '__fh_info', 'info', logging.INFO)
            prepare_handler(
                self.error_log, '__fh_error', 'error', logging.ERROR
            )

    def __set_email_logger(self):
        if not hasattr(self, 'mailer'):
            try:
                self.mailer = smtplib.SMTP(
                    self.config.get('email', {}).get('server', None),
                    self.config.get('email', {}).get('port', 587)
                )
            except gaierror as e:
                self.file(e, error=True)

    def __set_slack_logger(self):
        if not hasattr(self, 'slacker'):
            try:
                self.slacker = slacker.Slacker(
                    self.config.get('slack', {}).get('key', '')
                )
            except Exception as e:
                self.file(e, error=True)

file = {
    'info': os.environ.get('LOG_FILE_INFO'),
    'error': os.environ.get('LOG_FILE_ERROR')
}

email = {
    'server': os.environ.get('EMAIl_HOST'),
    'sendername': os.environ.get('EMAIL_SENDERNAME'),
    'from': os.environ.get('DEFAULT_FROM_EMAIL'),
    'to': os.environ.get('DEFAULT_EMAIL_TO'),
    'subject': 'TARS Error Logging',
    'user': os.environ.get('EMAIL_HOST_USER'),
    'password': os.environ.get('EMAIL_HOST_PASSWORD')
}

slack = {
    'key': os.environ.get('SLACK_APP_KEY'),
    'channel': os.environ.get('SLACK_CHANNEL'),
    'username': os.environ.get('SLACK_USERNAME')
}

trello = {
    'key': os.environ.get('TRELLO_KEY'),
    'token': os.environ.get('TRELLO_TOKEN'),
    'list': os.environ.get('TRELLO_LIST'),
    'cardname': os.environ.get('TRELLO_CARDNAME'),
    'members': (os.environ.get('TRELLO_MEMBERS_IDS'))
}

logger = Mann(
    console=True,
    file=file,
    email=email,
    slack=slack,
    trello=trello
)
