SELECT `mitarbeiter_id` FROM `arbeitsregel`, `mitarbeiter`
WHERE `mitarbeiter_id` = `mitarbeiter`.`id`
      AND `wochentag` = 'alle'
      AND `kalenderwoche` = 'alle'
      AND `beamter` = 'nein'
      AND `soll` = '07:48:00'
      AND `von` < '2017-12-1'
      AND (`bis` > '2017-12-1' OR `bis` IS NULL)