DROP TABLE IF EXISTS journals;
CREATE TABLE journals
(
    id              TEXT NOT NULL PRIMARY KEY,
    payload         BLOB NOT NULL,
    sequence_number INTEGER NOT NULL,
    actor_name      TEXT NOT NULL,
    created_at      TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    UNIQUE (id),
    UNIQUE (actor_name, sequence_number)
);

DROP TABLE IF EXISTS snapshots;
CREATE TABLE snapshots
(
    id              TEXT NOT NULL PRIMARY KEY,
    payload         BLOB NOT NULL,
    sequence_number INTEGER NOT NULL,
    actor_name      TEXT NOT NULL,
    created_at      TEXT NOT NULL DEFAULT (DATETIME('now', 'localtime')),
    UNIQUE (id),
    UNIQUE (actor_name, sequence_number)
);
