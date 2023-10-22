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

## Notes

- Brick/Money was used because I was worried about the uncertanties of floating point math as well as it adding the ability to support different currencies in the future. One example I found while testing was a rounding error with the red widget discount. The exception here is great in that it lets me know I have to decide how to handle rounding 32.95 / 2 = 16.475.

```
1) IntegrationTest::testRedAndRedWidget
Brick\Math\Exception\RoundingNecessaryException: Rounding is necessary to represent the result of the operation at this scale.
```
