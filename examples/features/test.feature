@http @rest @api
Feature: Do some tests

Scenario: Test dumper
  When I send a "GET" request to "/"
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "[1].foo" should be equal to "baaz"

Scenario: Test dumper with body
  When I create a "GET" request to "/"
    And I set the following body:
    """
{
  "foo": "bar"
}
    """
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "[1].foo" should be equal to "baaz"

@debug
Scenario: Test valid scenario + @debug + dumper
  When I send a "GET" request to "/"
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "[0].foo" should be equal to "bar"

@debug
Scenario: Test valid scenario + @debug + dumper with body
  When I create a "GET" request to "/"
    And I set the following body:
    """
{
  "foo": "bar"
}
    """
    And I send the request
  Then the status code should be 200
    And the response should be a valid json response
    And in the json, "[0].foo" should be equal to "bar"

Scenario Outline: Test dumper from outline
  When I send a "GET" request to "/"
  Then the status code should be <status>
    And the response should be a valid json response

  Examples:
      | status |
      | 200    |
      | 400    |
