-- # !
-- # { skygen
-- #   { load_islands
-- #     SELECT ownerUuid, level, x,y,z, gen_x, gen_y, gen_z FROM islands;
-- #   }
-- # }

-- # { save_island
-- # :ownerUuid string
-- # :level int
-- # :x int
-- # :y int
-- # :z int
-- # :gen_x int
-- # :gen_y int
-- # :gen_z int
-- # REPLACE INTO islands (owner_uuid, level, x, y, z, gen_x, gen_y, gen_z) VALUES (:ownerUuid, :level, :x, :y, :z, :gen_x, :gen_y, :gen_z);
-- # }
-- # { generators
-- # { init
-- # {
CREATE TABLE IF NOT EXISTS generators (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    island_uuid VARCHAR(36) NOT NULL,
    level INT NOT NULL DEFAULT 1,
    x INT NOT NULL,
    y INT NOT NULL,
    z INT NOT NULL
);
-- # }

-- # { load_all
-- # {
SELECT island_uuid, type, level, x, y, z FROM generators;
-- # }

-- # { save
-- # :island_uuid string
-- # :type string
-- # :level int
-- # :x int
-- # :y int
-- # :z int
-- # {
REPLACE INTO generators (island_uuid, type, level, x, y, z) VALUES (:island_uuid, :type, :level, :x, :y, :z);
-- # }
-- # }

-- # { regions
-- # { init
-- # {
CREATE TABLE IF NOT EXISTS regions (
    name VARCHAR(32) PRIMARY KEY,
    required_rank VARCHAR(32) DEFAULT NULL,
    min_x INT, min_y INT, min_z INT,
    max_x INT, max_y INT, max_z INT
);
-- # }

-- # { load_all
-- # {
SELECT * FROM regions;
-- # }

-- # { save
-- # :name string
-- # :required_rank string
-- # :min_x int
-- # :min_y int
-- # :min_z int
-- # :max_x int
-- # :max_y int
-- # :max_z int
-- # {
REPLACE INTO regions (name, required_rank, min_x, min_y, min_z, max_x, max_y, max_z) VALUES (:name, :required_rank, :min_x, :min_y, :min_z, :max_x, :max_y, :max_z);
-- # }
-- # }

-- # { delete
-- # :name string
-- # {
DELETE FROM regions WHERE name = :name;
-- # }
-- # }