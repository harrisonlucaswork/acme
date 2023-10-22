# Acme

Acme Widget Test
**Assumes you have docker and git repo downloaded**

1. Run the following command if running for the first time to build your docker image.

    ```bash
    bash app/build.sh
    ```

2. Run the following command after building your image. Don't forget to include the .env file.

    ```bash
    docker-compose --env-file .env up -d
    ```

3. Run the following command to get example output.

    ```bash
    docker exec -it test-acme-app-1 bash -c "php runner.php < ExampleRunnerInput.json"
    ```

4. Optionally copy new json input into container to run as tests.

    ```bash
    docker cp NewInput.json test-acme-app-1:/acme/NewInput.json
    docker exec -it test-acme-app-1 bash -c "php runner.php < NewInput.json"
    ```

## Notes for Andy

1. Brick/Money was used because I was worried about the uncertanties of floating point math as well as it adding the ability to support different currencies in the future. One example I found while testing was a rounding error with the red widget discount. The exception here is great in that it lets me know I have to decide how to handle rounding 32.95 / 2 = 16.475. I chose to round up here based on meeting the example expected outputs for this basket.
    > 1) IntegrationTest::testRedAndRedWidget Brick\Math\Exception\RoundingNecessaryException: Rounding is necessary to represent the result of the operation at this scale.

2. **Bug in example** I believe the basket example with R01 and R01 and a total of 54.37 should be 52.37

    ```text
    Items: R01, R01
    Subtotal: 65.90
    Shipping: 2.95
    Discount: 16.48
    Total: 52.37
    ```

3. The docker-compose isn't necessary here. I left it because I started off by thinking I would add some type of storage ie Redis/MySQL but ended up not getting to it. I also didn't want to tweak the devcontainer json.

4. I am not that happy with how the Rule logic turned out, specifically in the 'isSatisfiedBy' methods. I believe the Storage/Discount rules could likely be combined and push some of the condition logic into their own classes so we can add many more conditions without the switch statement etc.

5. 
