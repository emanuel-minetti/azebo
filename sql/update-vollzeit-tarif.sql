UPDATE arbeitsregel, (SELECT `mitarbeiter_id`
                      FROM `arbeitsregel`, `mitarbeiter`
                      WHERE `mitarbeiter_id` = `mitarbeiter`.`id`
                            AND `wochentag` = 'alle'
                            AND `kalenderwoche` = 'alle'
                            AND `beamter` = 'nein'
                            AND `soll` = '07:48:00'
                            AND `von` < '2017-12-1'
                            AND (`bis` > '2017-12-1' OR `bis` IS NULL)) AS vollzeit_tarif
SET soll = NULL
WHERE arbeitsregel.mitarbeiter_id = vollzeit_tarif.mitarbeiter_id
      AND  (arbeitsregel.bis > '2017-12-1' OR arbeitsregel.bis IS NULL)