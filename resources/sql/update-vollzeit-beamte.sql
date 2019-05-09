UPDATE arbeitsregel, (SELECT mitarbeiter_id FROM arbeitsregel, mitarbeiter
WHERE mitarbeiter.id = mitarbeiter_id
      AND beamter = 'ja'
      AND `von` < '2017-12-1'
      AND (`bis` > '2017-12-1' OR `bis` IS NULL)
      AND soll = '08:00:00') AS vollzeit_beamte
SET soll = NULL
WHERE arbeitsregel.mitarbeiter_id = vollzeit_beamte.mitarbeiter_id
      AND (arbeitsregel.bis > '2017-12-1' OR arbeitsregel.bis IS NULL)