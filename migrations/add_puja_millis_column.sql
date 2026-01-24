ALTER TABLE pujas
    ADD COLUMN fecha_puja_ms BIGINT NOT NULL DEFAULT 0 AFTER fecha_puja;
