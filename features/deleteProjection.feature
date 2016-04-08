Feature: Delete projection through command line

  @cli
  Scenario: Delete existing projection
    Given projection "projectionTestCaseDelete" exists
    When I run "eventstore:projection:delete" command with parameters and answer "yes":
      | name | projectionTestCaseDelete |
    Then the command exit code should be 200
    And I should see on console:
    """
    Continue with this action? Success! Projection was deleted.
    """
    And projection "projectionTestCaseDelete" should not exist

  @cli
  Scenario: Delete not existing projection
    When I run "eventstore:projection:delete" command with parameters and answer "yes":
      | name | projectionTestCaseDeleteNonExist |
    Then the command exit code should be 404
    And I should see on console:
    """
    Continue with this action? Error! Projection not found.
    """