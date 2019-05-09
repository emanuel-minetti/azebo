ALTER TABLE arbeitsmonat ADD gekappteMinuten INT NULL;
ALTER TABLE arbeitsmonat ADD gekappteStunden INT NULL;
ALTER TABLE arbeitsmonat
  MODIFY COLUMN gekappteStunden INT AFTER saldopositiv,
  MODIFY COLUMN gekappteMinuten INT AFTER gekappteStunden;