const fs = require('fs')
const sqlite3 = require('sqlite3').verbose()

const INPUT_JSON_FILE = "./test_prayer_time.json";
const OUTPUT_SQLITE_FILE = "./db.sqlite";

const TURN_ON_FOREIGN_KEY = "PRAGMA foreign_keys = ON;";

const DISTRICT_SCHEMA = `
CREATE TABLE district (
	district_id INTEGER PRIMARY KEY,
	name varchar(128) NOT NULL,

    UNIQUE(name) ON CONFLICT IGNORE
);`

const WAKT_TIMING_SCHEMA = `
CREATE TABLE wakt_timing (
	wakt_timing_id INTEGER PRIMARY KEY,
	district_id INTEGER NOT NULL,
	timing_of_date DATE NOT NULL,
	fazar_time VARCHAR(32) NOT NULL,
	zohar_time VARCHAR(32) NOT NULL,
	asar_time VARCHAR(32) NOT NULL,
	maghrib_time VARCHAR(32) NOT NULL,
	isha_time VARCHAR(32) NOT NULL,
	
    UNIQUE(district_id, timing_of_date) ON CONFLICT IGNORE,
	FOREIGN KEY (district_id) REFERENCES district (district_id) ON DELETE NO ACTION
);`;

const SEHRI_IFTARI_TIMGING_SCHEMA = `
CREATE TABLE sehri_iftari_timing (
	sehri_iftar_timing_id INTEGER PRIMARY KEY,
	district_id INTEGER NOT NULL,
	timing_of_date DATE NOT NULL,
	sehri_time VARCHAR(32) NOT NULL,
	iftari_time VARCHAR(32) NOT NULL,

    UNIQUE(district_id, timing_of_date) ON CONFLICT IGNORE,
	FOREIGN KEY (district_id) REFERENCES district (district_id)  ON DELETE NO ACTION
);`;

function SQLiteConnect(filename) {
    return new Promise((res, rej) => {
        let db = new sqlite3.Database(filename, (err) => {
            if (err) {
                rej(err);
            } else {
                res(db);
            }
        });
    });
}

function SQLiteRun(conn, sql, args) {
    return new Promise((res, rej) => {
        conn.run(sql, args, (err, rows) => {
            if (err) {
                rej(err);
            } else {
                res(rows);
            }
        });
    });
}

function SQLiteGet(conn, sql, args) {
    return new Promise((res, rej) => {
        conn.get(sql, args, (err, rows) => {
            if (err) {
                rej(err);
            } else {
                res(rows);
            }
        });
    });
}

function SQLiteClose(database) {
    return new Promise((res, rej) => {
        database.close((err) => {
            if (err) {
                rej(err);
            } else {
                res();
            }
        })
    });
}

async function main() {
    // deleting existing sqlite file
    try {
        await fs.unlinkSync(OUTPUT_SQLITE_FILE);
    } catch (err) {
        console.log("output file removal failed");
    }

    const data = await fs.readFileSync(INPUT_JSON_FILE, "utf-8");
    const jsonData = JSON.parse(data);

    const conn = await SQLiteConnect(OUTPUT_SQLITE_FILE);
    console.log("DB Connection created");

    console.log("Creating schema");
    let result = await SQLiteRun(conn, DISTRICT_SCHEMA, []);
    result = await SQLiteRun(conn, WAKT_TIMING_SCHEMA, []);
    result = await SQLiteRun(conn, SEHRI_IFTARI_TIMGING_SCHEMA, []);

    console.log("Turning foreign key on...");
    await SQLiteRun(conn, TURN_ON_FOREIGN_KEY, []);

    console.log("Inserting data...");
    let kk = 1;
    for (let index = 0; index < Object.keys(jsonData).length; index++) {
        const district = Object.keys(jsonData)[index];

        kk++;
        console.log(`Populating district : ${district}`);
        console.log(`Populating district : ${kk}`);

        await SQLiteRun(conn, 'INSERT INTO district (name) VALUES (?);', [district]);
        const row = await SQLiteGet(conn, 'SELECT district_id FROM district WHERE name = ?;', [district]);

        for (let timingIndex = 0; timingIndex < jsonData[district].length; timingIndex++) {
            const timing = jsonData[district][timingIndex];

            const date = timing["date"];
            const fazar = timing["fazar"];
            const zohar = timing["zohar"];
            const asar = timing["asar"];
            const maghrib = timing["maghrib"];
            const isha = timing["isha"];
            if (date && fazar && zohar && asar && maghrib && isha) {
                await SQLiteRun(conn, 'INSERT INTO wakt_timing(district_id, timing_of_date, fazar_time, zohar_time, asar_time, maghrib_time, isha_time) VALUES (?, ?, ?, ?, ?, ?, ?);', [row.district_id, date, fazar, zohar, asar, maghrib, isha]);
            }

            const sehri = timing["sehri"];
            const iftari = timing["iftar"];

            if (date && sehri && iftari && sehri != "NO" && iftari != "NO") {
                await SQLiteRun(conn, 'INSERT INTO sehri_iftari_timing(district_id, timing_of_date, sehri_time, iftari_time) VALUES (?, ?, ?, ?);', [row.district_id, date, sehri, iftari]);
            }
        }
    }
    await SQLiteClose(conn);
    console.log("DB Connection closed");
}

main();