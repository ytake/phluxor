CREATE DATABASE sample;
\c sample;

CREATE TABLE journals
(
    id              VARCHAR(26) NOT NULL,
    payload         BYTEA NOT NULL,
    sequence_number BIGINT,
    actor_name      VARCHAR(255),
    created_at      TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE (id),
    UNIQUE (actor_name, sequence_number)
);

CREATE TABLE snapshots
(
    id              VARCHAR(26) NOT NULL,
    payload         BYTEA NOT NULL,
    sequence_number BIGINT,
    actor_name      VARCHAR(255),
    created_at      TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE (id),
    UNIQUE (actor_name, sequence_number)
);
