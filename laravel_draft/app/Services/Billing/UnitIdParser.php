<?php



namespace App\Services\Billing;



class UnitIdParser

{

    /**

     * Parse a unit_id / room_no according to Unit ID Mapping Spec v1.2.

     *

     * @return array{colony_name:?string,department:?string,residence_type:?string,occupant_grade:?string,floor:?string,room_no:?string,matched:bool}

     */

    public static function parse(string $id): array

    {

        $id = strtoupper(trim($id));



        if ($id === '') {

            return self::empty(false);

        }



        if ($id === 'OUTSIDE' || preg_match('/^GYSER-\d+$/', $id) === 1) {

            return self::empty(false);

        }



        if (preg_match('/^WHK-(\d+)$/', $id, $m) === 1) {

            return self::result(

                'Weaving Hostel Kitchen',

                'Weaving',

                'COMMON',

                'COMMON',

                null,

                $m[1]

            );

        }



        if (preg_match('/^(SE|WE)-001-(\d+)$/', $id, $m) === 1) {

            $isSpinning = $m[1] === 'SE';



            return self::result(

                $isSpinning ? 'Spinning Executive Banglow' : 'Weaving Executive Banglow',

                $isSpinning ? 'Spinning' : 'Weaving',

                'HOUSE_A+',

                'SENIOR_STAFF',

                'Ground',

                '001-' . $m[2]

            );

        }



        $prefixes = [

            'SBC-H-' => ['Spinning Bachelor Colony (Hostel)', 'Spinning', 'ROOM', 'SENIOR_STAFF', 'hostel_embedded'],

            'NBC-H-' => ['New Bachelor Colony (Hostel)', 'Weaving', 'ROOM', 'SENIOR_STAFF', 'hostel_embedded'],

            'W-HOD-' => ['Weaving HOD Rooms', 'Weaving', 'ROOM', 'SENIOR_STAFF', 'single'],

            'CS-C-' => ['Admin Block', 'Centralized', 'CONTAINER', 'SENIOR_STAFF', 'single'],

            'CS-R-' => ['Admin Block', 'Centralized', 'ROOM', 'BACHELOR', 'single'],

            'CS-2-' => ['Old Abaseen Colony', 'Centralized', 'ROOM', 'BACHELOR', 'single'],

            'NBC-1-' => ['New Bachelor Colony', 'Spinning', 'ROOM', 'BACHELOR', 'fixed_1'],

            'NBC-2-' => ['New Bachelor Colony', 'Weaving', 'ROOM', 'BACHELOR', 'fixed_2'],

            'NBC-3-' => ['New Bachelor Colony', 'Weaving', 'ROOM', 'BACHELOR', 'fixed_3'],

            'NBC-4-' => ['New Bachelor Colony', 'Weaving', 'ROOM', 'BACHELOR', 'fixed_4'],

            'NBC-G-' => ['New Bachelor Colony', 'Weaving', 'ROOM', 'BACHELOR', 'fixed_ground'],

            'SBC-' => ['Spinning Bachelor Colony', 'Spinning', 'ROOM', 'BACHELOR', 'bachelor'],

            'WBC-' => ['Weaving Bachelor Colony', 'Weaving', 'ROOM', 'BACHELOR', 'bachelor'],

            'SH-' => ['Spinning Hostel', 'Spinning', 'ROOM', 'SENIOR_STAFF', 'hostel_sh'],

            'WH-' => ['Weaving Hostel', 'Weaving', 'ROOM', 'SENIOR_STAFF', 'hostel_wh'],

            'WE-' => ['Weaving A Type House', 'Weaving', 'HOUSE_A', 'FAMILY', 'house'],

            'SE-' => ['Spinning A Type House', 'Spinning', 'HOUSE_A', 'FAMILY', 'house'],

            'WA-' => ['Weaving A Type House', 'Weaving', 'HOUSE_A', 'FAMILY', 'house'],

            'SA-' => ['Spinning A Type House', 'Spinning', 'HOUSE_A', 'FAMILY', 'house'],

            'WB-' => ['Weaving B Type House', 'Weaving', 'HOUSE_B', 'FAMILY', 'house'],

            'SB-' => ['Spinning B Type House', 'Spinning', 'HOUSE_B', 'FAMILY', 'house'],

            'WC-' => ['Weaving C Type House', 'Weaving', 'HOUSE_C', 'FAMILY', 'house'],

            'SC-' => ['Spinning C Type House', 'Spinning', 'HOUSE_C', 'FAMILY', 'house'],

            'SPC-' => ['Spinning Palidar Colony', 'Spinning', 'ROOM', 'BACHELOR', 'single'],

            'PSSR-' => ['Private Security', 'External', 'ROOM', 'BACHELOR', 'single'],

        ];



        foreach ($prefixes as $prefix => [$colony, $department, $residenceType, $occupantGrade, $pattern]) {

            if (!str_starts_with($id, $prefix)) {

                continue;

            }



            $suffix = substr($id, strlen($prefix));

            $parsed = self::parseSuffix($pattern, $suffix);

            if ($parsed === null) {

                return self::empty(false);

            }



            return self::result(

                $colony,

                $department,

                $residenceType,

                $occupantGrade,

                $parsed['floor'],

                $parsed['room_no']

            );

        }



        return self::empty(false);

    }



    private static function empty(bool $matched = false): array

    {

        return [

            'colony_name' => null,

            'department' => null,

            'residence_type' => null,

            'occupant_grade' => null,

            'floor' => null,

            'room_no' => null,

            'matched' => $matched,

        ];

    }



    private static function result(?string $colony, ?string $department, ?string $residenceType, ?string $occupantGrade, ?string $floor, ?string $roomNo): array

    {

        return [

            'colony_name' => $colony,

            'department' => $department,

            'residence_type' => $residenceType,

            'occupant_grade' => $occupantGrade,

            'floor' => $floor,

            'room_no' => $roomNo,

            'matched' => true,

        ];

    }
    private static function floorLabel(?string $floor): string
    {
        return match ((string) $floor) {
            '1' => '1st',
            '2' => '2nd',
            '3' => '3rd',
            '4' => '4th',
            default => 'Ground',
        };
    }
    private static function parseSuffix(string $pattern, string $suffix): ?array

    {

        return match ($pattern) {

            'bachelor' => self::parseBachelor($suffix),

            'fixed_ground' => self::parseFixedFloor($suffix, 'Ground'),

            'fixed_1' => self::parseFixedFloor($suffix, '1'),

            'fixed_2' => self::parseFixedFloor($suffix, '2'),

            'fixed_3' => self::parseFixedFloor($suffix, '3'),

            'fixed_4' => self::parseFixedFloor($suffix, '4'),

            'hostel_embedded' => self::parseHostelEmbedded($suffix),

            'hostel_sh' => self::parseHostelSh($suffix),

            'hostel_wh' => self::parseWeavingHostel($suffix),

            'house' => self::parseHouse($suffix),

            'single' => self::parseSingle($suffix),

            default => null,

        };

    }



    private static function parseBachelor(string $suffix): ?array

    {

        if (preg_match('/^(G1|G|R[1-3]|[1-4])-(\d+)$/', $suffix, $m) !== 1) {

            return null;

        }



        $segment = $m[1];

        $floor = match (true) {

            $segment === 'G' || $segment === 'G1' => 'Ground',

            str_starts_with($segment, 'R') => self::floorLabel(substr($segment, 1)),

            default => self::floorLabel($segment),

        };



        return ['floor' => $floor, 'room_no' => $m[2]];

    }



    private static function parseFixedFloor(string $suffix, string $floor): ?array

    {

        if (preg_match('/^\d+(?:-\d+)*$/', $suffix) !== 1) {

            return null;

        }



        return ['floor' => self::floorLabel($floor), 'room_no' => $suffix];

    }



    private static function parseHostelEmbedded(string $suffix): ?array

    {

        if (preg_match('/^(\d)(\d{2})$/', $suffix, $m) !== 1) {

            return null;

        }



        return ['floor' => self::floorLabel($m[1]), 'room_no' => $m[2]];

    }



    private static function parseHostelSh(string $suffix): ?array
    {
        if (preg_match('/^(\d)(\d{2})$/', $suffix, $m) === 1) {
            return ['floor' => self::floorLabel($m[1]), 'room_no' => $m[2]];
        }

        return self::parseHostelExplicit($suffix);
    }

    private static function parseHostelExplicit(string $suffix): ?array

    {

        if (preg_match('/^(\d)-(\d)(\d{2})$/', $suffix, $m) !== 1) {

            return null;

        }



        if ($m[1] !== $m[2]) {

            return null;

        }



        return ['floor' => self::floorLabel($m[1]), 'room_no' => $m[3]];

    }



    private static function parseWeavingHostel(string $suffix): ?array

    {

        if (preg_match('/^(\d{3})$/', $suffix, $m) === 1) {

            return ['floor' => 'Ground', 'room_no' => (string) ((int) $m[1])];

        }



        return self::parseHostelExplicit($suffix);

    }



    private static function parseHouse(string $suffix): ?array

    {

        if (preg_match('/^([012])(\d{2})$/', $suffix, $m) !== 1) {

            return null;

        }



        return ['floor' => self::floorLabel($m[1]), 'room_no' => $m[2]];

    }



    private static function parseSingle(string $suffix): ?array

    {

        if (preg_match('/^\d+(?:-\d+)*$/', $suffix) !== 1) {

            return null;

        }



        return ['floor' => 'Ground', 'room_no' => $suffix];

    }

}