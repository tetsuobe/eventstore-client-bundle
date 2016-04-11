Feature: Update projection through command line

  Background:
    Given projection "projectionTestCaseUpdate" exists

  @cli
  Scenario: Update projection body
    When I run "eventstore:projection:update" command with parameters:
      | name | projectionTestCaseUpdate                                                                                         |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 0

  @cli
  Scenario: Update projection body with emit disables
    When I run "eventstore:projection:update" command with parameters:
      | name   | projectionTestCaseUpdate                                                                                         |
      | body   | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
      | --emit | false                                                                                                            |
    Then the command exit code should be 0

  @cli
  Scenario: Update not existing projection
    When I run "eventstore:projection:update" command with parameters:
      | name | projectionTestCaseUpdateNotExist                                                                                 |
      | body | fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}}) |
    Then the command exit code should be 404