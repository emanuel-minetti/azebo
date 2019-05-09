SELECT mitarbeiter_id FROM arbeitsregel, mitarbeiter
WHERE mitarbeiter.id = mitarbeiter_id
      AND beamter = 'ja'
      AND `von` < '2017-12-1'
      AND (`bis` > '2017-12-1' OR `bis` IS NULL)
      AND soll = '08:00:00'