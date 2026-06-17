<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facility_work_category_component_types')) {
            throw new RuntimeException('Facility work category component mapping table is missing.');
        }

        $approvedPairs = json_decode(<<<'JSON'
[
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Commode"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Wash Basin"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Faucet / Tap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Drain"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Water Line"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Eastern WC Pan / Indian Toilet Seat"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Western Commode / Toilet Seat"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Commode Seat Cover"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Commode Tank / Cistern"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Concealed Cistern"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank Lid"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank Push Button"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank Lever Handle"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank Float Valve / Ball Cock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank Inlet Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Tank Outlet Valve / Flush Mechanism"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Pipe Rubber Seal"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flush Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "WC Connector / Pan Collar"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "WC Trap / P-Trap / S-Trap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Commode Fixing Bolts"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Commode Base Sealant / Joint"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Wall Mounted Urinal"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Floor Urinal Channel"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Flush Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Flush Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Waste Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Trap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Partition"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Sensor"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Urinal Drain Cover / Jali"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pedestal Wash Basin"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Wall Mounted Wash Basin"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Counter Top Wash Basin"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Vanity Basin"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Sink"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Pedestal"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Bracket"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Tap / Pillar Cock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Mixer"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Waste Coupling"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Waste Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bottle Trap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "P-Trap for Basin"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Flexible Connection Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Angle Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Basin Overflow Cover / Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Vanity Cabinet"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Counter Slab / Basin Marble"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bibcock / Bib Tap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Long Body Bibcock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Short Body Bibcock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pillar Cock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Two Way Bibcock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Sink Cock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Swan Neck Tap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Wall Mixer"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Mixer"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Concealed Mixer"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Stop Cock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Concealed Stop Cock"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Angle Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Gate Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Ball Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Float Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Non-Return Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pressure Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Extension Nipple"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Wall Flange"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flexible Connection Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Flexible Hose Joint"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Tap Handle / Knob"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Tap Cartridge"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Tap Washer / Rubber"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Tap Aerator / Nozzle"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Head Shower"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Overhead Shower Arm"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Rain Shower"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Rose"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Hand Shower"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Hand Shower Holder"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Hand Shower Flexible Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Diverter"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Control Knob"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Tray"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bath Tub"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bath Tub Mixer"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bath Tub Waste Outlet"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bath Curtain Rod"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Bath Curtain"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Glass Partition"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Shower Partition Door"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Muslim Shower / Jet Spray"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Muslim Shower Gun"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Muslim Shower Flexible Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Muslim Shower Holder"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Muslim Shower Angle Valve"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Health Faucet"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Lota Tap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Lota / Water Mug"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Ablution Tap / Wazu Tap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Floor Trap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Floor Drain"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Drain Cover / Jali"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Nahani Trap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Gully Trap"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Waste Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Soil Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Sewer Line"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Drainage Line"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Cleanout Point"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Inspection Chamber"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Manhole Cover"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Vent Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Joint"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Trap Seal / Rubber Joint"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Drain Channel"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Grating / Channel Cover"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Cold Water Supply Line"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Hot Water Supply Line"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Main Water Inlet Line"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Distribution Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Concealed Water Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Exposed Water Pipe"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Elbow"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Tee"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Socket / Coupling"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Union"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Clamp / Support"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Pipe Nipple"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Leakage Point"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Water Pressure Point"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Water Meter Connection Point"
    },
    {
        "work_category_name": "Plumbing & Drainage Works",
        "component_name": "Supply Isolation Valve"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Exhaust Fan"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Light"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Geyser Indicator Light"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Geyser Switch"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Geyser Plug / Socket"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Ceiling Light"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Wall Light"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "LED Light"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Bulb Holder"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Tube Light"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Light Switch"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Exhaust Fan Switch"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Socket / Power Point"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Plug Top"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Wiring Point"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Junction Box"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "MCB / Breaker Point"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Exhaust Fan Grill"
    },
    {
        "work_category_name": "Electrical & Lighting Works",
        "component_name": "Exhaust Fan Duct"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Wall Tiles"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Floor Tiles"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Skirting Tiles"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Tile Joint / Grouting"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Floor Slope"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Waterproofing Layer"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Ceiling / False Ceiling"
    },
    {
        "work_category_name": "Civil & Masonry Works",
        "component_name": "Plaster / Wall Surface"
    },
    {
        "work_category_name": "Metalwork / Welding / Fabrication",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Pole / Outdoor Structure Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Carpentry / Furniture / Wood Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Painting / Coating / Finishing",
        "component_name": "Paint / Coating"
    },
    {
        "work_category_name": "HVAC / Ventilation / Cooling",
        "component_name": "Exhaust Fan"
    },
    {
        "work_category_name": "HVAC / Ventilation / Cooling",
        "component_name": "Exhaust Fan Grill"
    },
    {
        "work_category_name": "HVAC / Ventilation / Cooling",
        "component_name": "Exhaust Fan Duct"
    },
    {
        "work_category_name": "HVAC / Ventilation / Cooling",
        "component_name": "Ventilator Window"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Water Line"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Cold Water Supply Line"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Hot Water Supply Line"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Main Water Inlet Line"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Distribution Pipe"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Concealed Water Pipe"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Exposed Water Pipe"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Pipe Elbow"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Pipe Tee"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Pipe Socket / Coupling"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Pipe Union"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Pipe Clamp / Support"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Pipe Nipple"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Leakage Point"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Water Pressure Point"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Water Meter Connection Point"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Supply Isolation Valve"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Electric Geyser"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Gas Geyser"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Instant Geyser"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Inlet Pipe"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Outlet Pipe"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Flexible Pipe"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Angle Valve"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Safety Valve"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Thermostat"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Heating Element"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Indicator Light"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Switch"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Plug / Socket"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Mounting Bracket"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Drain Valve"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Geyser Leakage Point"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Tap"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Tap Line"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Bench / Seat"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Drain Channel"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Drain Jali"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Floor Tiles"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Partition"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Foot Rest"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Wazu Water Pressure Valve"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Cleaning Water Point"
    },
    {
        "work_category_name": "Water Supply / RO / Pump / Geyser",
        "component_name": "Cleaning Hose Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Commode"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Wash Basin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Faucet / Tap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Mirror"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Exhaust Fan"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Drain"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Light"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Water Line"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Geyser"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Other Component"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Eastern WC Pan / Indian Toilet Seat"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Western Commode / Toilet Seat"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Commode Seat Cover"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Commode Tank / Cistern"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Concealed Cistern"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank Lid"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank Push Button"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank Lever Handle"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank Float Valve / Ball Cock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank Inlet Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Tank Outlet Valve / Flush Mechanism"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Pipe Rubber Seal"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flush Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "WC Connector / Pan Collar"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "WC Trap / P-Trap / S-Trap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Commode Fixing Bolts"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Commode Base Sealant / Joint"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Wall Mounted Urinal"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Floor Urinal Channel"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Flush Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Flush Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Waste Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Trap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Partition"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Sensor"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Urinal Drain Cover / Jali"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Pedestal Wash Basin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Wall Mounted Wash Basin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Counter Top Wash Basin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Vanity Basin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Sink"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Pedestal"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Bracket"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Tap / Pillar Cock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Mixer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Waste Coupling"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Waste Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bottle Trap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "P-Trap for Basin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Flexible Connection Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Angle Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Basin Overflow Cover / Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Vanity Cabinet"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Counter Slab / Basin Marble"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bibcock / Bib Tap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Long Body Bibcock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Short Body Bibcock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Pillar Cock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Two Way Bibcock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Sink Cock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Swan Neck Tap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Wall Mixer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Mixer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Concealed Mixer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Stop Cock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Concealed Stop Cock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Angle Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Gate Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Ball Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Float Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Non-Return Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Pressure Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Extension Nipple"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Wall Flange"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flexible Connection Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Flexible Hose Joint"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Tap Handle / Knob"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Tap Cartridge"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Tap Washer / Rubber"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Tap Aerator / Nozzle"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Head Shower"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Overhead Shower Arm"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Rain Shower"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Rose"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Hand Shower"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Hand Shower Holder"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Hand Shower Flexible Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Diverter"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Control Knob"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Tray"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bath Tub"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bath Tub Mixer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bath Tub Waste Outlet"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bath Curtain Rod"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bath Curtain"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Glass Partition"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Shower Partition Door"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Muslim Shower / Jet Spray"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Muslim Shower Gun"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Muslim Shower Flexible Pipe"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Muslim Shower Holder"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Muslim Shower Angle Valve"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Health Faucet"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Lota Tap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Lota / Water Mug"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Ablution Tap / Wazu Tap"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Washroom Door"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Frame / Chowkhat"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Hinges"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Handle"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Lock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Latch / Kundi"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Bolt"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Closer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Stopper"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Door Vent / Louver"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Toilet Cubicle Partition"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Partition Door"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Partition Lock / Indicator Lock"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Partition Hinges"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Privacy Screen"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Mirror Frame"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Mirror Shelf"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Soap Dish"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Liquid Soap Dispenser"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Tissue Paper Holder"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Toilet Roll Holder"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Towel Rod"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Towel Hook"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Clothes Hook"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Bucket"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Mug"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Dustbin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Sanitary Bin"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Hand Dryer"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Paper Towel Dispenser"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Air Freshener Holder"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Cleaning Brush Holder"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Foot Mat"
    },
    {
        "work_category_name": "Washroom & Sanitation Facilities",
        "component_name": "Sign Board"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Bucket"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Mug"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Dustbin"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Sanitary Bin"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Hand Dryer"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Paper Towel Dispenser"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Air Freshener Holder"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Cleaning Brush Holder"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Foot Mat"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Cleaning Water Point"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Cleaning Hose Pipe"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Mop Stand / Cleaning Holder"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Garbage Collection Point"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Sanitizer Dispenser"
    },
    {
        "work_category_name": "Cleaning & Housekeeping Services",
        "component_name": "Odour Control Unit"
    },
    {
        "work_category_name": "Pest Control / Fumigation",
        "component_name": "Pest Trap Point"
    },
    {
        "work_category_name": "Road / Pavement / Drain / Ground Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Roofing / Shade / Canopy Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Washroom Door"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Frame / Chowkhat"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Hinges"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Handle"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Lock"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Latch / Kundi"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Bolt"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Closer"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Stopper"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Door Vent / Louver"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Toilet Cubicle Partition"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Partition Door"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Partition Lock / Indicator Lock"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Partition Hinges"
    },
    {
        "work_category_name": "Doors / Gates / Locks / Access Works",
        "component_name": "Privacy Screen"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Shower Glass Partition"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Shower Partition Door"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Toilet Cubicle Partition"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Partition Door"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Partition Lock / Indicator Lock"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Partition Hinges"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Privacy Screen"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Ventilator Window"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Window Glass"
    },
    {
        "work_category_name": "Glass / Aluminium / Partition Works",
        "component_name": "Window Latch"
    },
    {
        "work_category_name": "Garden / Fountain / Irrigation Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Fire & Safety Facility Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Signage / Branding / Marking Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "Moving / Installation / Dismantling Works",
        "component_name": "Other Fixture / Component"
    },
    {
        "work_category_name": "General Facility Maintenance",
        "component_name": "Other Component"
    },
    {
        "work_category_name": "General Facility Maintenance",
        "component_name": "Other Fixture / Component"
    }
]
JSON, true, 512, JSON_THROW_ON_ERROR);

        if (count($approvedPairs) !== 411) {
            throw new RuntimeException('Approved facility category-component seed row count must be exactly 411.');
        }

        $categoryIds = DB::table('facility_work_categories')
            ->where('is_active', 1)
            ->pluck('id', 'name');

        $componentIds = DB::table('facility_component_types')
            ->where('is_active', 1)
            ->pluck('id', 'name');

        $now = now();
        $insertRows = [];
        $resolvedPairs = [];

        foreach ($approvedPairs as $pair) {
            $categoryName = $pair['work_category_name'];
            $componentName = $pair['component_name'];

            if (!isset($categoryIds[$categoryName])) {
                throw new RuntimeException('Missing active work category: '.$categoryName);
            }

            if (!isset($componentIds[$componentName])) {
                throw new RuntimeException('Missing active component type: '.$componentName);
            }

            $key = $categoryIds[$categoryName].'|'.$componentIds[$componentName];

            if (isset($resolvedPairs[$key])) {
                throw new RuntimeException('Duplicate resolved category-component mapping: '.$key);
            }

            $resolvedPairs[$key] = true;
            $insertRows[] = [
                'work_category_id' => $categoryIds[$categoryName],
                'component_type_id' => $componentIds[$componentName],
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($insertRows): void {
            DB::table('facility_work_category_component_types')->insertOrIgnore($insertRows);
        });

        $activeRows = DB::table('facility_work_category_component_types')
            ->where('is_active', 1)
            ->count();

        if ($activeRows !== 411) {
            throw new RuntimeException('Facility category-component mappings active row count is not exactly 411.');
        }
    }

    public function down(): void
    {
    }
};
