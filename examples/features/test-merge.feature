@http @rest @api
Feature: Do tests on merged steps

Scenario: Testing positive equal
  When I send a "GET" request to "/"
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "[0].foo" should be equal to "bar"

Scenario: Testing not equal
  When I send a "GET" request to "/"
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "[0].foo" should not be equal to "baz"

Scenario: Test array
    When I send a "GET" request to "/"
    Then the status code should be 200
      And the response should be a valid json response
      And in the json, the root should be an array
      And in the json, "[2].foo" should be an array

Scenario: Test count
    When I send a "GET" request to "/"
    Then the status code should be 200
      And the response should be a valid json response
      And in the json, the root collection should have 3 elements
      And in the json, "[2].foo" collection should have 3 elements

Scenario: Test count max
    When I send a "GET" request to "/"
    Then the status code should be 200
      And the response should be a valid json response
      And in the json, the root collection should have at most 10 elements
      And in the json, "[2].foo" collection should have at most 3 elements

Scenario: Test count max
    When I send a "GET" request to "/"
    Then the status code should be 200
      And the response should be a valid json response
      And in the json, the root collection should have at least 2 elements
      And in the json, "[2].foo" collection should have at least 2 elements
