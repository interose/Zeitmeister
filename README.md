# Zeitmeister
Zeitmeister is a simple symfony application to track working hours via apple shortcuts. It offers a REST API interface which could be accessed via apple shortcuts.

# Screenshot
![Dashboard](/doc/image.png)

# REST API
```
curl --request POST \
  --url https://<your_domain_here>/api/time/add \
  --header 'authorization: Bearer <your_token_here>' \
  --header 'content-type: application/json' \
  --data '{
  "ts": "yyyy-mm-dd H:i:s",
  "eventType": "checkin"
}'
```

```
curl --request POST \
  --url https://<your_domain_here>/api/time/add \
  --header 'authorization: Bearer <your_token_here>' \
  --header 'content-type: application/json' \
  --data '{
  "ts": "yyyy-mm-dd H:i:s",
  "eventType": "checkout"
}'
```

# Todo
- Add possibility to edit time entries
- User management, currently users have to added manually
- Add possibility to add missing days (holiday, trade fair, etc ...)
