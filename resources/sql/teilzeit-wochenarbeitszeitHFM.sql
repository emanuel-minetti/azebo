SELECT mitarbeiter_id,
  mitarbeiter.benutzername,
  kalenderwoche,
  TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC( `soll`))),'%H:%i') AS soll_gesamt,
  TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC( `soll`)) * 1.01025641026),'%H:%i') AS soll_neu,
  TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC( `soll`)) * 0.01025641026), '%H:%i')  AS soll_diff
FROM arbeitsregel, mitarbeiter, Azebo_HFM_27_11_2017
WHERE (soll IS NOT NULL
       AND soll != '00:00:00'
       AND von <= '2017-12-1'
       AND (bis >= '2017-12-1' OR bis IS NULL)
       AND mitarbeiter.id = mitarbeiter_id
       AND mitarbeiter.benutzername = Azebo_HFM_27_11_2017.benutzername)
GROUP BY mitarbeiter_id, kalenderwoche;
