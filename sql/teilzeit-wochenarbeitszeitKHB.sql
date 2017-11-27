SELECT mitarbeiter_id,
  mitarbeiter.benutzername,
  kalenderwoche,
  TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC( `soll`))),'%H:%i') AS soll_gesamt
FROM arbeitsregel, mitarbeiter, Azebo_KHB_27_11_2017
WHERE (soll IS NOT NULL
       AND soll != '00:00:00'
       AND von <= '2017-12-1'
       AND (bis >= '2017-12-1' OR bis IS NULL)
       AND mitarbeiter.id = mitarbeiter_id
       AND mitarbeiter.benutzername = Azebo_KHB_27_11_2017.benutzername)
GROUP BY mitarbeiter_id, kalenderwoche;
