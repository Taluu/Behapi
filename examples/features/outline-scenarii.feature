@http @rest @api
Feature: Do some more tests on outline scenarii

Background: Test with background
  When I send a "GET" request to "/204"

@debug
Scenario: Test valid scenario + @debug + dumper with body
  When I create a "GET" request to "/400/Bad Request"
    And I set the following body:
    """
    {
      "foo": "bar"
    }
    """
    And I send the request
  Then the status code should be 400
    And the response should be a valid json response
    And in the json, "[0].foo" should be equal to "bar"

Scenario Outline: Test dumper from outline
  When I send a "GET" request to "/<status>/<message>"
  Then the status code should be 200
    And the response should be a valid json response

  Examples:
      | status | message     |
      | 200    | OK          |
      | 400    | Bad Request |
