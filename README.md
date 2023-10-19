# acme
Acme Widget Test

1. Run the following command if running for the first time to build your docker image.

```bash
bash app/build.sh
```

2. Run the following command after building your image. Don't forget to include the .env file.

```bash
docker-compose --env-file .env up -d
```
