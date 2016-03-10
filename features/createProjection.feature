Feature: Creating new projection through command line

  @cli
  Scenario: Create arbitrary projection
    Given projection "projectionTestCaseCreate" does not exist
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseCreate                                                                                         |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 0
    And I should see on console:
    """
    Success! Projection was created.
    """
    And projection "projectionTestCaseCreate" should be created

  @cli
  Scenario: Create arbitrary projection with the name as existing one
    Given projection "projectionTestCaseCreateExists" exists
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseCreateExists                                                                                   |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 409
    And I should see on console:
    """
    Conflict
    """

  @cli
  Scenario: Create arbitrary projection without name argument
    Given projection "projectionTestCaseNoName" does not exist
    When I run "eventstore:projection:create" command with parameters:
      | name |                                                                                                                  |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    And I should see on console:
    """
    Missing projection <info>name</info>.
    """

  @cli
  Scenario: Create arbitrary projection without body argument
    Given projection "projectionTestCaseNoBody" does not exist
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseNoBody |
      | body |                          |
    And I should see on console:
    """
    Missing projection body, it should be added via <info>body</info> argument or <info>--file</info> option.
    """

  @cli
  Scenario: Force create projection with the same name as existing one
    Given projection "projectionTestCaseForce" exists
    When I run "eventstore:projection:create" command with parameters:
      | name    | projectionTestCaseForce"                                                                                         |
      | body    | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
      | --force | true                                                                                                             |
    Then the command exit code should be 0
    And I should see on console:
    """
    Success! Projection was created.
    """
    And projection "projectionTestCaseCreate" should be created