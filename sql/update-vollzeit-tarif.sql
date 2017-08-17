UPDATE arbeitsregel, (SELECT `mitarbeiter_id`
                      FROM `arbeitsregel`, `mitarbeiter`
                      WHERE `mitarbeiter_id` = `mitarbeiter`.`id`
                            AND `wochentag` = 'alle'
                            AND `kalenderwoche` = 'alle'
                            AND `beamter` = 'nein'
                            AND `soll` = '07:48:00'
                            AND `von` < '2017-1-12'
                            AND (`bis` > '2017-1-12' OR `bis` IS NULL)) AS vollzeit_tarif
SET soll = NULL
WHERE arbeitsregel.mitarbeiter_id = vollzeit_tarif.mitarbeiter_id
      AND  (`bis` > '2017-1-12' OR `bis` IS NULL)