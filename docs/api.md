# Routes


- Route name is next to URI.
- All trailing slashes are optional.
- The given return payloads below are indicative and only provide the bare minimum, so you know how to see success and errors from the frontend. They are usually enhanced with all sort of metadata from `djangorestframework-jsonapi`.
- In JSON payloads/bodies, type is denoted ECMAScript style (ala Typescript), except for `<things_within_chevron>`meaning `object` value with some basic explanation of the expected content, and `?` next to optional keys.

---

### Admin

##### Route
- `GET /admin/` (admin)

##### Discussion
Not part of the RESTful API. Renders view for Django admin.

---

### Registration and authentication

- `POST /api/v1/register/` (register)

##### Parameters

```javascript
{username:string, email?:string, password:string, confirm_password:string}
```

##### Returns
`{user:<serialized_user>, token:string}` on success with status `200`.
You get a `400` on errors with a payload similar to the following example:

```javascript
{'user': {'email': [u'Enter a valid e-mail address.']}, 'created': [u'This field is required.']}
```

- `POST /api/v1/auth/login/` (login)

##### Parameters
```javascript
{username:string, password:string}
```
