UPDATE mitarbeiter, (SELECT mitarbeiter_id FROM arbeitsregel, mitarbeiter
WHERE mitarbeiter.id = mitarbeiter_id
      AND beamter = 'ja'
      AND `von` < '2017-1-12'
      AND (`bis` > '2017-1-12' OR `bis` IS NULL)
      AND soll = '08:00:00') AS vollzeit_tarif
SET vollzeit = 'ja'
WHERE mitarbeiter.id = `mitarbeiter_id`