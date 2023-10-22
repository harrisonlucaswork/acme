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

2. The docker-compose isn't necessary here. I left it because I started off by thinking I would add some type of storage ie Redis/MySQL but ended up not getting to it. I also didn't want to tweak the devcontainer json.

3. I am not that happy with how the Rule logic turned out, specifically in the 'isSatisfiedBy' methods. I believe the Storage/Discount rules could likely be combined and push some of the condition logic into their own classes so we can add many more conditions without the switch statement etc. Additionally, the cart's getDiscountTotal and getShippingTotal are basically the same and can be combined.

## Todo

0. Rename the 'price' input for discount as its currently actually a percentage relative to the item being discounted. ie 0.5 for G01 will give 50% discount relative to the cost of G01.

1. Apply the Repository pattern for Products, Discounts, and Shipping rules so they can be moved to persistant storage instead of just an array without breaking things.

2. Make the conditions that satisfy a rule easier to add more or change. Probably its own class for each rule type instead of constants in a switch (ugly). Could maybe factory them and have the rule simply see if all conditions are met to determine isSatisfiedBy.

3. PHPStan has mostly return type has no value type specified in iterable type errors which I didn't take the time to fix everywhere. However, it did point out a potentially unsafe - operator in ShippingRule so I fixed that but it makes me want to sort out #2 even more.

4. I wanted to try a simple service container implementation and have the backend be automatically injected but did not have time. I saw this article a while ago and have been wanting to try it in a small project. <https://ryangjchandler.co.uk/posts/build-your-own-service-container-in-php-minimal-container>

5. Expand upon the runner. Should be able to support multiple types of input ie file/stdin. Possibly different formats or at least break up the json into different parts ie add-products could be separate persistant operation separate from adding things to cart and calculating totals.

6. Nice error handling if data is bad or something goes wrong. This assumes good data for the most part.
