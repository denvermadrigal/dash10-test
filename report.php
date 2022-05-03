<?php

/**
 * Use this file to output reports required for the SQL Query Design test.
 * An example is provided below. You can use the `asTable` method to pass your query result to,
 * to output it as a styled HTML table.
 */

$database = 'nba2019';
require_once('vendor/autoload.php');
require_once('include/utils.php');

/*
 * Example Query
 * -------------
 * Retrieve all team codes & names
 */
echo '<h1>Example Query</h1>';
$teamSql = "SELECT * FROM team";
$teamResult = query($teamSql);
// dd($teamResult);
echo asTable($teamResult);

/*
 * Report 1
 * --------
 * Produce a query that reports on the best 3pt shooters in the database that are older than 30 years old. Only 
 * retrieve data for players who have shot 3-pointers at greater accuracy than 35%.
 * 
 * Retrieve
 *  - Player name
 *  - Full team name
 *  - Age
 *  - Player number
 *  - Position
 *  - 3-pointers made %
 *  - Number of 3-pointers made 
 *
 * Rank the data by the players with the best % accuracy first.
 */
echo '<h1>Report 1 - Best 3pt Shooters</h1>';
// write your query here
$best3pterSql = '
    select roster.name as "Player name",
           team.name as "Full team name",
           pt.age as "Age",
           roster.number as "Player number",
           roster.pos as "Position",
           concat(round(pt.3pt/pt.3pt_attempted*100, 2), "%") as "3-pointers made %",
           pt.3pt,
           pt.3pt_attempted
      from player_totals as pt
      left join roster ON pt.player_id = roster.id
      left join team ON roster.team_code = team.code
     where pt.age > 30
       and round(pt.3pt/pt.3pt_attempted*100, 2) > 35
     order by round(pt.3pt/pt.3pt_attempted*100, 2) desc
';
$best3pterResult = query($best3pterSql);
echo asTable($best3pterResult);

/*
 * Report 2
 * --------
 * Produce a query that reports on the best 3pt shooting teams. Retrieve all teams in the database and list:
 *  - Team name
 *  - 3-pointer accuracy (as 2 decimal place percentage - e.g. 33.53%) for the team as a whole,
 *  - Total 3-pointers made by the team
 *  - # of contributing players - players that scored at least 1 x 3-pointer
 *  - of attempting player - players that attempted at least 1 x 3-point shot
 *  - total # of 3-point attempts made by players who failed to make a single 3-point shot.
 * 
 * You should be able to retrieve all data in a single query, without subqueries.
 * Put the most accurate 3pt teams first.
 */
echo '<h1>Report 2 - Best 3pt Shooting Teams</h1>';
// write your query here
$team3ptSql = '
    select team.name as "Team Name", 
           concat(round(sum(player_totals.3pt)/sum(player_totals.3pt_attempted)*100, 2), "%") as "3-Pointer Accuracy",
           sum(player_totals.3pt) as "Total 3-pointers made by the team",
           sum(case when player_totals.3pt > 0 then 1 else 0 end) as "# of contributing players",
           sum(case when player_totals.3pt_attempted > 0 then 1 else 0 end) as "players that attempted at least 1 x 3-point shot",
           sum(case when (player_totals.3pt_attempted > 0 and player_totals.3pt < 1) then 1 else 0 end) as "total # of 3-point attempts by players who failed to make a 3-point shot"
      from player_totals
      left join roster ON player_totals.player_id = roster.id
      left join team ON roster.team_code = team.code
     group by team_code
     order by round(sum(player_totals.3pt_attempted)/sum(player_totals.3pt), 2) desc
';
$team3ptResult = query($team3ptSql);
echo asTable($team3ptResult);
