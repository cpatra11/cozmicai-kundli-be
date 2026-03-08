This handbook provides detailed instructions for using the Kebrya Astrology and Horoscope API. It covers available endpoints, expected requests, responses, and additional configurations.


## Introduction
This API enables users to compute various astrological charts and information levels based on given parameters. It covers different calendar conversions such as Gregorian, Jalali.



### Calculate Gregorian Chart
**Endpoint**: `POST /api/chart`

**Description**: Calculates a birth chart based on the Gregorian calendar date.

**Request Body**:
```json
{
    "latitude": "35.708309",
    "longitude": "51.380730",
    "year": 1991,
    "month": 11,
    "day": 1,
    "hour": 7,
    "min": 45,
    "sec": 0,
    "varga": ["D1", "D9"],
    "nesting": 2,
    "time_zone": "+03:30",
    "infolevel": ["basic"]
}
```

**Response**:
```json
{
    "chart": {
        "user": {
            "datetime": "2011-09-11 11:11:11",
            "timezone": "-04:30",
            "longitude": 35.708309,
            "latitude": 51.38073,
            "altitude": 0,
            "location": {
                "lat": "35.708309",
                "lon": "51.380730",
                "dms": {
                    "lat": {
                        "deg": 35,
                        "min": 42,
                        "sec": 29.91239999999964
                    },
                    "lon": {
                        "deg": 51,
                        "min": 22,
                        "sec": 50.62799999999934
                    }
                }
            }
        },
        "graha": {
            "Su": {
                "longitude": 144.5402069,
                "latitude": 0.0001407,
                "speed": 0.9720046,
                "ascension": 169.482529,
                "declination": 4.5247084,
                "rashi": 5,
                "degree": 24.540206899999987,
                "nakshatra": {
                    "anga": "nakshatra",
                    "key": 11,
                    "ratio": 1,
                    "abhijit": false,
                    "left": 15.948448250000146,
                    "name": "Poorva Phalguni",
                    "pada": 4
                },
                "astangata": null,
                "rashiAvastha": "swa",
                "vargottama": false,
                "yuddha": null,
                "latitude_dms": {
                    "deg": 0,
                    "min": 0,
                    "sec": 0.50652
                },
                "longitude_dms": {
                    "deg": 144,
                    "min": 32,
                    "sec": 24.74483999995303
                },
                "speed_dms": {
                    "deg": 0,
                    "min": 58,
                    "sec": 19.21655999999996
                },
                "degree_dms": {
                    "deg": 24,
                    "min": 32,
                    "sec": 24.74483999995303
                },
                "rashi_name": "Leo"
            },
            "Mo": {
                "longitude": 316.2885569,
                "latitude": 4.9355835,
                "speed": 12.1702957,
                "ascension": 339.9565302,
                "declination": -3.129211,
                "rashi": 11,
                "degree": 16.288556900000003,
                "nakshatra": {
                    "anga": "nakshatra",
                    "key": 24,
                    "ratio": 1,
                    "abhijit": false,
                    "left": 27.835823250000082,
                    "name": "Shatabhisha",
                    "pada": 3
                },
                "astangata": false,
                "rashiAvastha": "neutral",
                "vargottama": true,
                "yuddha": null,
                "latitude_dms": {
                    "deg": 4,
                    "min": 56,
                    "sec": 8.100599999999591
                },
                "longitude_dms": {
                    "deg": 316,
                    "min": 17,
                    "sec": 18.804840000011637
                },
                "speed_dms": {
                    "deg": 12,
                    "min": 10,
                    "sec": 13.064520000001812
                },
                "degree_dms": {
                    "deg": 16,
                    "min": 17,
                    "sec": 18.804840000011637
                },
                "rashi_name": "Aquarius"
            },
            ...
        },
        "bhava": {
            "1": {
                "longitude": 315.7261187,
                "ascension": 318.1864939,
                "declination": -16.1209955,
                "rashi": 11,
                "degree": 15.726118699999972
            },
            "2": {
                "longitude": 345.7261187,
                "ascension": 346.8612365,
                "declination": -5.6279737,
                "rashi": 12,
                "degree": 15.726118699999972
            },
            ...
        },
        "lagna": {
            "Lg": {
                "longitude": 315.7261187,
                "ascension": 318.1864939,
                "declination": -16.1209955,
                "rashi": 11,
                "degree": 15.726118699999972,
                "nakshatra": {
                    "anga": "nakshatra",
                    "key": 24,
                    "ratio": 1,
                    "abhijit": false,
                    "left": 32.054109750000315,
                    "name": "Shatabhisha",
                    "pada": 3
                }
            },
            "MLg": {
                "longitude": 238.0017464,
                "ascension": 235.7445208,
                "declination": -19.7136636,
                "rashi": 8,
                "degree": 28.001746400000002
            }
        },
        "varga": {
            "D9": {
                "bhava": {
                    "1": {
                        "rashi": 11,
                        "degree": 21.535068299999747,
                        "longitude": 321.53506829999975
                    },
                    "2": {
                        "rashi": 12,
                        "degree": 21.535068299999747,
                        "longitude": 351.53506829999975
                    },
                    ...
                },
                "graha": {
                    "Su": {
                        "rashi": 8,
                        "degree": 10.861862099999874,
                        "speed": 0.9720046,
                        "longitude": 220.86186209999988
                    },
                    "Mo": {
                        "rashi": 11,
                        "degree": 26.597012100000022,
                        "speed": 12.1702957,
                        "longitude": 326.59701210000003
                    },
                   ...
                },
                "lagna": {
                    "Lg": {
                        "rashi": 11,
                        "degree": 21.535068299999747,
                        "longitude": 321.53506829999975
                    },
                    "MLg": {
                        "rashi": 12,
                        "degree": 12.015717600000006,
                        "longitude": 342.0157176
                    }
                }
            }
        },
        "dasha": {
            "nesting": 0,
            "type": "vimshottari",
            "key": "",
            "duration": 3786834240,
            "start": "1998-09-15 03:06:48",
            "end": "2118-09-15 05:30:48",
            "periods": {
                "Ra": {
                    "nesting": 1,
                    "type": "mahadasha",
                    "key": "Ra",
                    "duration": 568025136,
                    "start": "1998-09-15 03:06:48",
                    "end": "2016-09-14 11:52:24",
                    "periods": {
                        "Ra": {
                            "nesting": 2,
                            "type": "antardasha",
                            "key": "RaRa",
                            "duration": 85203770,
                            "start": "1998-09-15 03:06:48",
                            "end": "2001-05-28 06:49:38"
                        },
                        "Gu": {
                            "nesting": 2,
                            "type": "antardasha",
                            "key": "RaGu",
                            "duration": 75736685,
                            "start": "2001-05-28 06:49:38",
                            "end": "2003-10-21 20:47:43"
                        },
                       ...
                    }
                },
            }
        },
        "houses": {
            "1": {
                "graha": {
                    "Mo": {
                        "longitude": 316.2885569,
                        "latitude": 4.9355835,
                        "speed": 12.1702957,
                        "ascension": 339.9565302,
                        "declination": -3.129211,
                        "rashi": 11,
                        "degree": 16.288556900000003,
                        "nakshatra": {
                            "anga": "nakshatra",
                            "key": 24,
                            "ratio": 1,
                            "abhijit": false,
                            "left": 27.835823250000082,
                            "name": "Shatabhisha",
                            "pada": 3
                        },
                        "astangata": false,
                        "rashiAvastha": "neutral",
                        "vargottama": true,
                        "yuddha": null,
                        "latitude_dms": {
                            "deg": 4,
                            "min": 56,
                            "sec": 8.100599999999591
                        },
                        "longitude_dms": {
                            "deg": 316,
                            "min": 17,
                            "sec": 18.804840000011637
                        },
                        "speed_dms": {
                            "deg": 12,
                            "min": 10,
                            "sec": 13.064520000001812
                        },
                        "degree_dms": {
                            "deg": 16,
                            "min": 17,
                            "sec": 18.804840000011637
                        },
                        "rashi_name": "Aquarius"
                    }
                }
            },
            "2": {
                "graha": []
                ...
            }
            ...
        }
    },
    "duration_of_response": 0.022,
    "created_at": "2024-07-16 11:56:01"
}
```

### Calculate Transit Chart
**Endpoint**: `POST /api/transit-chart`

**Description**: Calculates a transit chart based on the Gregorian calendar date.

**Request Body**:
```json
{
    "latitude": "35.708309",
    "longitude": "51.380730",
    "year": 2011,
    "month": 9,
    "day": 11,
    "hour": 11,
    "min": 11,
    "sec": 11,
    "t_year": 2023,
    "t_month": 11,
    "t_day": 1,
    "t_hour": 7,
    "t_min": 45,
    "t_sec": 0,
    "time_zone": "-04:30"
}
```


**Response**:
```json
{
    "chart": {
        "user": {
            "datetime": "2011-09-11 11:11:11",
            "timezone": "-04:30",
            "longitude": 35.708309,
            "latitude": 51.38073,
            "altitude": 0,
            "location": {
                "lat": "35.708309",
                "lon": "51.380730",
                "dms": {
                    "lat": {
                        "deg": 35,
                        "min": 42,
                        "sec": 29.91239999999964
                    },
                    "lon": {
                        "deg": 51,
                        "min": 22,
                        "sec": 50.62799999999934
                    }
                }
            }
        },
        "graha": {
            "Su": {
                "longitude": 144.5402069,
                "latitude": 0.0001407,
                "speed": 0.9720046,
                "ascension": 169.482529,
                "declination": 4.5247084,
                "rashi": 5,
                "degree": 24.540206899999987,
                "nakshatra": {
                    "anga": "nakshatra",
                    "key": 11,
                    "ratio": 1,
                    "abhijit": false,
                    "left": 15.948448250000146,
                    "name": "Poorva Phalguni",
                    "pada": 4
                },
                "astangata": null,
                "rashiAvastha": "swa",
                "vargottama": false,
                "yuddha": null,
                "latitude_dms": {
                    "deg": 0,
                    "min": 0,
                    "sec": 0.50652
                },
                "longitude_dms": {
                    "deg": 144,
                    "min": 32,
                    "sec": 24.74483999995303
                },
                "speed_dms": {
                    "deg": 0,
                    "min": 58,
                    "sec": 19.21655999999996
                },
                "degree_dms": {
                    "deg": 24,
                    "min": 32,
                    "sec": 24.74483999995303
                },
                "rashi_name": "Leo"
            },
            "Mo": {
                "longitude": 316.2885569,
                "latitude": 4.9355835,
                "speed": 12.1702957,
                "ascension": 339.9565302,
                "declination": -3.129211,
                "rashi": 11,
                "degree": 16.288556900000003,
                "nakshatra": {
                    "anga": "nakshatra",
                    "key": 24,
                    "ratio": 1,
                    "abhijit": false,
                    "left": 27.835823250000082,
                    "name": "Shatabhisha",
                    "pada": 3
                },
                "astangata": false,
                "rashiAvastha": "neutral",
                "vargottama": true,
                "yuddha": null,
                "latitude_dms": {
                    "deg": 4,
                    "min": 56,
                    "sec": 8.100599999999591
                },
                "longitude_dms": {
                    "deg": 316,
                    "min": 17,
                    "sec": 18.804840000011637
                },
                "speed_dms": {
                    "deg": 12,
                    "min": 10,
                    "sec": 13.064520000001812
                },
                "degree_dms": {
                    "deg": 16,
                    "min": 17,
                    "sec": 18.804840000011637
                },
                "rashi_name": "Aquarius"
            },
            ...
        },
        "bhava": {
            "1": {
                "longitude": 315.7261187,
                "ascension": 318.1864939,
                "declination": -16.1209955,
                "rashi": 11,
                "degree": 15.726118699999972
            },
            "2": {
                "longitude": 345.7261187,
                "ascension": 346.8612365,
                "declination": -5.6279737,
                "rashi": 12,
                "degree": 15.726118699999972
            },
            ...
        },
        "lagna": {
            "Lg": {
                "longitude": 315.7261187,
                "ascension": 318.1864939,
                "declination": -16.1209955,
                "rashi": 11,
                "degree": 15.726118699999972,
                "nakshatra": {
                    "anga": "nakshatra",
                    "key": 24,
                    "ratio": 1,
                    "abhijit": false,
                    "left": 32.054109750000315,
                    "name": "Shatabhisha",
                    "pada": 3
                }
            },
            "MLg": {
                "longitude": 238.0017464,
                "ascension": 235.7445208,
                "declination": -19.7136636,
                "rashi": 8,
                "degree": 28.001746400000002
            }
        },
        "transit": {
            "user": {
                "datetime": "2023-11-01 07:45:00",
                "timezone": "+03:30",
                "longitude": 35.708309,
                "latitude": 51.38073,
                "altitude": 0,
                "date": {
                    "year": "2023",
                    "month": "11",
                    "day": "01"
                },
                "time": {
                    "hour": "07",
                    "min": "45",
                    "sec": "00"
                }
            },
            "graha": {
                "Su": {
                    "longitude": 194.2870235,
                    "latitude": 0.0002536,
                    "speed": 0.9997967,
                    "ascension": 216.1001466,
                    "declination": -14.3273797,
                    "rashi": 7,
                    "degree": 14.287023500000004,
                    "latitude_dms": {
                        "deg": 0,
                        "min": 0,
                        "sec": 0.91296
                    },
                    "longitude_dms": {
                        "deg": 194,
                        "min": 17,
                        "sec": 13.28460000001317
                    },
                    "speed_dms": {
                        "deg": 0,
                        "min": 59,
                        "sec": 59.268120000000124
                    },
                    "degree_dms": {
                        "deg": 14,
                        "min": 17,
                        "sec": 13.28460000001317
                    },
                    "rashi_name": "Libra"
                },
                "Mo": {
                    "longitude": 56.4997617,
                    "latitude": 4.3624192,
                    "speed": 13.0857484,
                    "ascension": 79.5256518,
                    "declination": 27.4607468,
                    "rashi": 2,
                    "degree": 26.4997617,
                    "latitude_dms": {
                        "deg": 4,
                        "min": 21,
                        "sec": 44.70911999999907
                    },
                    "longitude_dms": {
                        "deg": 56,
                        "min": 29,
                        "sec": 59.142120000001896
                    },
                    "speed_dms": {
                        "deg": 13,
                        "min": 5,
                        "sec": 8.694239999999825
                    },
                    "degree_dms": {
                        "deg": 26,
                        "min": 29,
                        "sec": 59.142120000001896
                    },
                    "rashi_name": "Taurus"
                },
                ...
            }
        },
        "dasha": {
            "nesting": 0,
            "type": "vimshottari",
            "key": "",
            "duration": 3786834240,
            "start": "1998-09-15 03:06:48",
            "end": "2118-09-15 05:30:48",
            "periods": {
                "Ra": {
                    "nesting": 1,
                    "type": "mahadasha",
                    "key": "Ra",
                    "duration": 568025136,
                    "start": "1998-09-15 03:06:48",
                    "end": "2016-09-14 11:52:24",
                    "periods": {
                        "Ra": {
                            "nesting": 2,
                            "type": "antardasha",
                            "key": "RaRa",
                            "duration": 85203770,
                            "start": "1998-09-15 03:06:48",
                            "end": "2001-05-28 06:49:38"
                        },
                        "Gu": {
                            "nesting": 2,
                            "type": "antardasha",
                            "key": "RaGu",
                            "duration": 75736685,
                            "start": "2001-05-28 06:49:38",
                            "end": "2003-10-21 20:47:43"
                        },
                       ...
                    }
                },
            }
        },
        "houses": {
            "1": {
                "graha": {
                    "Mo": {
                        "longitude": 316.2885569,
                        "latitude": 4.9355835,
                        "speed": 12.1702957,
                        "ascension": 339.9565302,
                        "declination": -3.129211,
                        "rashi": 11,
                        "degree": 16.288556900000003,
                        "nakshatra": {
                            "anga": "nakshatra",
                            "key": 24,
                            "ratio": 1,
                            "abhijit": false,
                            "left": 27.835823250000082,
                            "name": "Shatabhisha",
                            "pada": 3
                        },
                        "astangata": false,
                        "rashiAvastha": "neutral",
                        "vargottama": true,
                        "yuddha": null,
                        "latitude_dms": {
                            "deg": 4,
                            "min": 56,
                            "sec": 8.100599999999591
                        },
                        "longitude_dms": {
                            "deg": 316,
                            "min": 17,
                            "sec": 18.804840000011637
                        },
                        "speed_dms": {
                            "deg": 12,
                            "min": 10,
                            "sec": 13.064520000001812
                        },
                        "degree_dms": {
                            "deg": 16,
                            "min": 17,
                            "sec": 18.804840000011637
                        },
                        "rashi_name": "Aquarius"
                    }
                }
            },
            "2": {
                "graha": []
            }
            ...
        }
    },
    "duration_of_response": 0.022,
    "created_at": "2024-07-16 11:56:01"
}
```

### Calculate Transit Jalali Chart
**Endpoint**: `POST /api/jalali/transit-chart`

**Description**: Calculates a transit chart based on the Jalali calendar date.

**Request Body**:
```json
{
    "latitude": "35.708309",
    "longitude": "51.380730",
    "year": 1370,
    "month": 8,
    "day": 10,
    "hour": 7,
    "min": 45,
    "sec": 0,
    "t_year": 1402,
    "t_month": 4,
    "t_day": 23,
    "t_hour": 7,
    "t_min": 45,
    "t_sec": 0,
    "time_zone": "+03:30"
}
```

**Response**: Same as [Calculate Transit Chart](#calculate-transit-chart).

### Calculate Panchanga
**Endpoint**: `POST /api/panchanga`

**Description**: Calculates details from Panchanga, which is a Vedic astrological calendar system used to determine auspicious times and other important elements that influence daily life. It includes the five elements of time: Tithi (Lunar day), Nakshatra (Star), Yoga (a special calculation using the Sun and Moon’s positions), Karana (half of a Tithi), and Vara (weekday).

**Request Body**:
```json
{
    "latitude": "35.708309",
    "longitude": "51.380730",
    "year": 1991,
    "month": 11,
    "day": 1,
    "hour": 7,
    "min": 45,
    "sec": 0,
    "varga": ["D1"],
    "nesting": 0,
    "time_zone": "+03:30",
    "infolevel": ["panchanga"]
}
```

**Response**: Same as [Calculate Gregorian Chart](#calculate-gregorian-chart).

### Calculate Jalali Chart
**Endpoint**: `POST /api/jalali/chart`

**Description**: Calculates a birth chart based on the Jalali calendar date, which is used in Persian culture. The Jalali calendar is a solar calendar, similar to the Gregorian calendar but with different month names and lengths.

**Request Body**:
```json
{
    "latitude": "35.708309",
    "longitude": "51.380730",
    "year": 1370,
    "month": 8,
    "day": 10,
    "hour": 7,
    "min": 45,
    "sec": 0,
    "varga": ["D1"],
    "nesting": 2,
    "time_zone": "+03:30",
    "infolevel": []
}
```

**Response**: Same as [Calculate Gregorian Chart](#calculate-gregorian-chart).



### Get Now Chart
**Endpoint**: `GET /api/now`

**Description**: Retrieves the astrological chart for the current time and specified location. Useful for real-time astrological readings and predictions.

**Query Parameters**:
- `timezone`: The timezone string (e.g., `Asia/Tehran`)
- `latitude`: Location latitude (e.g., `35.708309`)
- `longitude`: Location longitude (e.g., `51.380730`)

**Response**: Same as [Calculate Gregorian Chart](#calculate-gregorian-chart).

## Input Parameters

### Latitude and Longitude
- **Latitude**
  - Indicates the geographical latitude of the location for the birth or event.
  - Example: `35.708309`
- **Longitude**
  - Indicates the geographical longitude of the location for the birth or event.
  - Example: `51.380730`

### Date and Time
- **Year, Month, Day**
  - Represents the date of birth or event in the specified calendar.
  - This trio of parameters identifies the exact astronomical positions of celestial bodies at a particular time.
  - Example (Gregorian): `1991`, `11`, `1`
  - Example (Jalali): `1370`, `8`, `10`
  - Example (Hijri): `1442`, `2`, `23`
- **Hour, Minute, Second**
  - Represents the exact time of birth or event.
  - Time is crucial in determining the ascendant (Lagna) and the precise positioning of the planets.
  - Example: `7`, `45`, `0`

### Varga (Divisional Charts)
- **Varga**
  - A list of divisional charts to be calculated. These charts provide insights into different aspects of life.
  - Common Vargas: `D1` (Rāśi chart, also known as the natal chart or birth chart), `D9` (Navāṁśa chart, used primarily for assessing marriage and spiritual inclinations).
  - Example: `["D1", "D9"]`
  - Each Varga chart magnifies specific aspects of life and highlights how planetary influences are distributed across different life areas.

### Nesting
- **Nesting**
  - The depth of nested computations. This parameter affects the detail level vishottari dasha.
  - if set to `0` dasha will not return.
  - Example: `0`, `2`, `4`

### Time Zone
- **Time Zone**
  - The time zone of the birthplace or event location.
  - Time zone corrections ensure the accurate placement of planetary positions. 
  - Example: `+03:30` (for Tehran)
  - Avaibale Timezones:
    - Africa/Abidjan
    - Africa/Accra
    - Africa/Addis_Ababa
    - Africa/Algiers
    - Africa/Asmara
    - Africa/Bamako
    - Africa/Bangui
    - Africa/Banjul
    - Africa/Bissau
    - Africa/Blantyre
    - Africa/Brazzaville
    - Africa/Bujumbura
    - Africa/Cairo
    - Africa/Casablanca
    - Africa/Ceuta
    - Africa/Conakry
    - Africa/Dakar
    - Africa/Dar_es_Salaam
    - Africa/Djibouti
    - Africa/Douala
    - Africa/El_Aaiun
    - Africa/Freetown
    - Africa/Gaborone
    - Africa/Harare
    - Africa/Johannesburg
    - Africa/Juba
    - Africa/Kampala
    - Africa/Khartoum
    - Africa/Kigali
    - Africa/Kinshasa
    - Africa/Lagos
    - Africa/Libreville
    - Africa/Lome
    - Africa/Luanda
    - Africa/Lubumbashi
    - Africa/Lusaka
    - Africa/Malabo
    - Africa/Maputo
    - Africa/Maseru
    - Africa/Mbabane
    - Africa/Mogadishu
    - Africa/Monrovia
    - Africa/Nairobi
    - Africa/Ndjamena
    - Africa/Niamey
    - Africa/Nouakchott
    - Africa/Ouagadougou
    - Africa/PortoNovo
    - Africa/Sao_Tome
    - Africa/Tripoli
    - Africa/Tunis
    - Africa/Windhoek
    - America/Adak
    - America/Anchorage
    - America/Anguilla
    - America/Antigua
    - America/Araguaina
    - America/ArgentinaBuenos_Aires
    - America/ArgentinaCatamarca
    - America/ArgentinaCordoba
    - America/ArgentinaJujuy
    - America/ArgentinaLa_Rioja
    - America/ArgentinaMendoza
    - America/ArgentinaRio_Gallegos
    - America/ArgentinaSalta
    - America/ArgentinaSan_Juan
    - America/ArgentinaSan_Luis
    - America/ArgentinaTucuman
    - America/ArgentinaUshuaia
    - America/Aruba
    - America/Asuncion
    - America/Atikokan
    - America/Bahia
    - America/Bahia_Banderas
    - America/Barbados
    - America/Belem
    - America/Belize
    - America/BlancSablon
    - America/Boa_Vista
    - America/Bogota
    - America/Boise
    - America/Cambridge_Bay
    - America/Campo_Grande
    - America/Cancun
    - America/Caracas
    - America/Cayenne
    - America/Cayman
    - America/Chicago
    - America/Chihuahua
    - America/Ciudad_Juarez
    - America/Costa_Rica
    - America/Creston
    - America/Cuiaba
    - America/Curacao
    - America/Danmarkshavn
    - America/Dawson
    - America/Dawson_Creek
    - America/Denver
    - America/Detroit
    - America/Dominica
    - America/Edmonton
    - America/Eirunepe
    - America/El_Salvador
    - America/Fort_Nelson
    - America/Fortaleza
    - America/Glace_Bay
    - America/Goose_Bay
    - America/Grand_Turk
    - America/Grenada
    - America/Guadeloupe
    - America/Guatemala
    - America/Guayaquil
    - America/Guyana
    - America/Halifax
    - America/Havana
    - America/Hermosillo
    - America/IndianaIndianapolis
    - America/IndianaKnox
    - America/IndianaMarengo
    - America/IndianaPetersburg
    - America/IndianaTell_City
    - America/IndianaVevay
    - America/IndianaVincennes
    - America/IndianaWinamac
    - America/Inuvik
    - America/Iqaluit
    - America/Jamaica
    - America/Juneau
    - America/KentuckyLouisville
    - America/KentuckyMonticello
    - America/Kralendijk
    - America/La_Paz
    - America/Lima
    - America/Los_Angeles
    - America/Lower_Princes
    - America/Maceio
    - America/Managua
    - America/Manaus
    - America/Marigot
    - America/Martinique
    - America/Matamoros
    - America/Mazatlan
    - America/Menominee
    - America/Merida
    - America/Metlakatla
    - America/Mexico_City
    - America/Miquelon
    - America/Moncton
    - America/Monterrey
    - America/Montevideo
    - America/Montserrat
    - America/Nassau
    - America/New_York
    - America/Nome
    - America/Noronha
    - America/North_DakotaBeulah
    - America/North_DakotaCenter
    - America/North_DakotaNew_Salem
    - America/Nuuk
    - America/Ojinaga
    - America/Panama
    - America/Paramaribo
    - America/Phoenix
    - America/Portau
    - America/Port_of_Spain
    - America/Porto_Velho
    - America/Puerto_Rico
    - America/Punta_Arenas
    - America/Rankin_Inlet
    - America/Recife
    - America/Regina
    - America/Resolute
    - America/Rio_Branco
    - America/Santarem
    - America/Santiago
    - America/Santo_Domingo
    - America/Sao_Paulo
    - America/Scoresbysund
    - America/Sitka
    - America/St_Barthelemy
    - America/St_Johns
    - America/St_Kitts
    - America/St_Lucia
    - America/St_Thomas
    - America/St_Vincent
    - America/Swift_Current
    - America/Tegucigalpa
    - America/Thule
    - America/Tijuana
    - America/Toronto
    - America/Tortola
    - America/Vancouver
    - America/Whitehorse
    - America/Winnipeg
    - America/Yakutat
    - Antarctica/Casey
    - Antarctica/Davis
    - Antarctica/DumontDUrville
    - Antarctica/Macquarie
    - Antarctica/Mawson
    - Antarctica/McMurdo
    - Antarctica/Palmer
    - Antarctica/Rothera
    - Antarctica/Syowa
    - Antarctica/Troll
    - Antarctica/Vostok
    - Arctic/Longyearbyen
    - Asia/Aden
    - Asia/Almaty
    - Asia/Amman
    - Asia/Anadyr
    - Asia/Aqtau
    - Asia/Aqtobe
    - Asia/Ashgabat
    - Asia/Atyrau
    - Asia/Baghdad
    - Asia/Bahrain
    - Asia/Baku
    - Asia/Bangkok
    - Asia/Barnaul
    - Asia/Beirut
    - Asia/Bishkek
    - Asia/Brunei
    - Asia/Chita
    - Asia/Choibalsan
    - Asia/Colombo
    - Asia/Damascus
    - Asia/Dhaka
    - Asia/Dili
    - Asia/Dubai
    - Asia/Dushanbe
    - Asia/Famagusta
    - Asia/Gaza
    - Asia/Hebron
    - Asia/Ho_Chi_Minh
    - Asia/Hong_Kong
    - Asia/Hovd
    - Asia/Irkutsk
    - Asia/Jakarta
    - Asia/Jayapura
    - Asia/Jerusalem
    - Asia/Kabul
    - Asia/Kamchatka
    - Asia/Karachi
    - Asia/Kathmandu
    - Asia/Khandyga
    - Asia/Kolkata
    - Asia/Krasnoyarsk
    - Asia/Kuala_Lumpur
    - Asia/Kuching
    - Asia/Kuwait
    - Asia/Macau
    - Asia/Magadan
    - Asia/Makassar
    - Asia/Manila
    - Asia/Muscat
    - Asia/Nicosia
    - Asia/Novokuznetsk
    - Asia/Novosibirsk
    - Asia/Omsk
    - Asia/Oral
    - Asia/Phnom_Penh
    - Asia/Pontianak
    - Asia/Pyongyang
    - Asia/Qatar
    - Asia/Qostanay
    - Asia/Qyzylorda
    - Asia/Riyadh
    - Asia/Sakhalin
    - Asia/Samarkand
    - Asia/Seoul
    - Asia/Shanghai
    - Asia/Singapore
    - Asia/Srednekolymsk
    - Asia/Taipei
    - Asia/Tashkent
    - Asia/Tbilisi
    - Asia/Tehran
    - Asia/Thimphu
    - Asia/Tokyo
    - Asia/Tomsk
    - Asia/Ulaanbaatar
    - Asia/Urumqi
    - Asia/UstNera
    - Asia/Vientiane
    - Asia/Vladivostok
    - Asia/Yakutsk
    - Asia/Yangon
    - Asia/Yekaterinburg
    - Asia/Yerevan
    - Atlantic/Azores
    - Atlantic/Bermuda
    - Atlantic/Canary
    - Atlantic/Cape_Verde
    - Atlantic/Faroe
    - Atlantic/Madeira
    - Atlantic/Reykjavik
    - Atlantic/South_Georgia
    - Atlantic/St_Helena
    - Atlantic/Stanley
    - Australia/Adelaide
    - Australia/Brisbane
    - Australia/Broken_Hill
    - Australia/Darwin
    - Australia/Eucla
    - Australia/Hobart
    - Australia/Lindeman
    - Australia/Lord_Howe
    - Australia/Melbourne
    - Australia/Perth
    - Australia/Sydney
    - Europe/Amsterdam
    - Europe/Andorra
    - Europe/Astrakhan
    - Europe/Athens
    - Europe/Belgrade
    - Europe/Berlin
    - Europe/Bratislava
    - Europe/Brussels
    - Europe/Bucharest
    - Europe/Budapest
    - Europe/Busingen
    - Europe/Chisinau
    - Europe/Copenhagen
    - Europe/Dublin
    - Europe/Gibraltar
    - Europe/Guernsey
    - Europe/Helsinki
    - Europe/Isle_of_Man
    - Europe/Istanbul
    - Europe/Jersey
    - Europe/Kaliningrad
    - Europe/Kirov
    - Europe/Kyiv
    - Europe/Lisbon
    - Europe/Ljubljana
    - Europe/London
    - Europe/Luxembourg
    - Europe/Madrid
    - Europe/Malta
    - Europe/Mariehamn
    - Europe/Minsk
    - Europe/Monaco
    - Europe/Moscow
    - Europe/Oslo
    - Europe/Paris
    - Europe/Podgorica
    - Europe/Prague
    - Europe/Riga
    - Europe/Rome
    - Europe/Samara
    - Europe/San_Marino
    - Europe/Sarajevo
    - Europe/Saratov
    - Europe/Simferopol
    - Europe/Skopje
    - Europe/Sofia
    - Europe/Stockholm
    - Europe/Tallinn
    - Europe/Tirane
    - Europe/Ulyanovsk
    - Europe/Vaduz
    - Europe/Vatican
    - Europe/Vienna
    - Europe/Vilnius
    - Europe/Volgograd
    - Europe/Warsaw
    - Europe/Zagreb
    - Europe/Zurich
    - Indian/Antananarivo
    - Indian/Chagos
    - Indian/Christmas
    - Indian/Cocos
    - Indian/Comoro
    - Indian/Kerguelen
    - Indian/Mahe
    - Indian/Maldives
    - Indian/Mauritius
    - Indian/Mayotte
    - Indian/Reunion
    - Pacific/Apia
    - Pacific/Auckland
    - Pacific/Bougainville
    - Pacific/Chatham
    - Pacific/Chuuk
    - Pacific/Easter
    - Pacific/Efate
    - Pacific/Fakaofo
    - Pacific/Fiji
    - Pacific/Funafuti
    - Pacific/Galapagos
    - Pacific/Gambier
    - Pacific/Guadalcanal
    - Pacific/Guam
    - Pacific/Honolulu
    - Pacific/Kanton
    - Pacific/Kiritimati
    - Pacific/Kosrae
    - Pacific/Kwajalein
    - Pacific/Majuro
    - Pacific/Marquesas
    - Pacific/Midway
    - Pacific/Nauru
    - Pacific/Niue
    - Pacific/Norfolk
    - Pacific/Noumea
    - Pacific/Pago_Pago
    - Pacific/Palau
    - Pacific/Pitcairn
    - Pacific/Pohnpei
    - Pacific/Port_Moresby
    - Pacific/Rarotonga
    - Pacific/Saipan
    - Pacific/Tahiti
    - Pacific/Tarawa
    - Pacific/Tongatapu
    - Pacific/Wake
    - Pacific/Wallis

### Info Level
- **Info Level**
  - Specifies the levels of information to be included in the response. It can include basic details, planetary strengths, and more.
  - Possible values: `["basic"]`, `["panchanga"]`, `["transit"]`
  - Example: `["basic", "yogas", "grahabala"]`
  - The info level determines the granularity of the data returned, from fundamental positional aspects to advanced yogas and planetary strengths (bala).
