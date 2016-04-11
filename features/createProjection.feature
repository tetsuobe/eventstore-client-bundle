Feature: Creating new projection through command line

  @cli
  Scenario: Create arbitrary projection
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseCreate                                                                                         |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 0
    And projection "projectionTestCaseCreate" should be created

  @cli
  Scenario: Create arbitrary projection
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseCreateFile                                                                                         |
      | --file | ./fixtures/tmp/projection.js |
    Then the command exit code should be 0
    And projection "projectionTestCaseCreateFile" should be created

  @cli
  Scenario: Create arbitrary projection with the name as existing one
    Given projection "projectionTestCaseCreateExists" exists
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseCreateExists                                                                                   |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 409

  @cli
  Scenario: Create arbitrary projection without name argument
    When I run "eventstore:projection:create" command with parameters:
      | name |                                                                                                                  |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 400

  @cli
  Scenario: Create arbitrary projection without body argument
    When I run "eventstore:projection:create" command with parameters:
      | name | projectionTestCaseNoBody |
      | body |                          |
    Then the command exit code should be 400

  @cli
  Scenario: Force create projection with the same name as existing one
    Given projection "projectionTestCaseForce" exists
    When I run "eventstore:projection:create" command with parameters:
      | name    | projectionTestCaseForce"                                                                                         |
      | body    | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
      | --force | true                                                                                                             |
    Then the command exit code should be 0
    And projection "projectionTestCaseForce" should be updated