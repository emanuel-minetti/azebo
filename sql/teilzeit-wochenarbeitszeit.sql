SELECT benutzername,
  TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC( `soll`))),'%H:%i') AS soll_gesamt_in_sec
FROM arbeitsregel, mitarbeiter
WHERE (soll IS NOT NULL
       AND soll != '00:00:00'
       AND von <= '2017-12-1'
       AND (bis >= '2017-12-1' OR bis IS NULL)
       AND mitarbeiter.id = mitarbeiter_id)
GROUP BY mitarbeiter_id;
