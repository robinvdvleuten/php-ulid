# Changelog

All notable changes to `ulid` will be documented in this file

## v4.0.2 - 2019-09-24

- Use case-insensitive character lookup when decoding time: 637ca0822ce72cb108552450bb738828621703cd

## v4.0.1 - 2019-07-22

- Use milliseconds intead of seconds when generating ULIDs: 28dfc9285affa01385c175bb7fb5289791980edb

## v4.0.0 - 2019-06-27

- ULID instance can be converted to timestamp: e858e3e0888c8b26f7282edf5956441846f780de
- Throw exception when ULID string is invalid: 8018f5fe08699e1003c625d39a0fbac5060a0f33
- Use canonical URL for ulid/javascript: #15
- Use typecast instead of intval: #14

## v3.0.0 - 2019-01-20

- Dropped support for PHP 7.0 and lower: b6d6512c58234b6f52faa8eb0a41bc041f526422
- Exploit PHP 7.1 features: #7 
- Test repo with PHP 7.3: #13 
- Update $lastGenTime when generating ULID: 84eb606534529d3804c7a515359a78952d135fcb

## v2.0.0 - 2018-10-30

- Output in uppercase by default: #4
- Small changes to make PHP5.5+ compatible: #5

## v1.1.0 - 2017-09-04

- Simple static method to create instance from ulid string: 467e28852dc462b776abb21f9b7555398ebca79a
- Use same calculation as origin library: 9cb26f8192ce04a1ab8c75775d9689be891805b5

## v1.0.0 - 2017-07-13

- initial release
