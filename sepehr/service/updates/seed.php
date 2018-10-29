<?php namespace Sepehr\Service\Updates;

use Sepehr\Service\Models\Service;
use Faker;
use October\Rain\Database\Updates\Seeder;

class SeedAllTable extends Seeder
{

    public function run()
    {
        $faker = Faker\Factory::create();
        $service = new Service();

        $service->user_id = 1;
        $service->manager_id = 1;
        $service->sender_postal_code = $faker->postcode();
        $service->sender_address = $faker->address();
        $service->status_id = $faker->numberBetween(1, 6);

        foreach ($service->packages as $package) {
            $package['receiver_postal_code'] = $faker->postcode();
            $package['receiver_address'] = $faker->address();
            $package['weight_id'] = $faker->numberBetween(1, 3);
            $package['post_type_id'] = $faker->numberBetween(1, 3);
            $package['distribution_time_id'] = $faker->numberBetween(1, 3);
            $package['special_services_id'] = $faker->numberBetween(1, 3);
            $package['price'] = 0;
            $package['package_type_id'] = $faker->numberBetween(1, 3);
            $package['insurance_type_id'] = $faker->numberBetween(1, 3);
        }
        $service->save();
        /*Service::create([
            'user_id' => 1,
            'manager_id' => 1,
            'sender_postal_code' => $faker->postcode(),
            'sender_address' => $faker->address($nbWords = 3),
            'status_id' => $faker->numberBetween(1,5),
            foreach ($service->packages as $package){}
            'packages[receiver_postal_code]' => $faker->postcode(),
            'packages[receiver_address]' => $faker->address(),
            'packages[weight_id]' => 1,
            'packages[post_type_id]' => $faker->numberBetween(1,2),
            'packages[distribution_time_id]' => $faker->numberBetween(1,2),
            'packages[special_services_id]' => $faker->numberBetween(1,2),
            'packages[price]' => '',
            'packages[package_type_id]' => $faker->numberBetween(1,2),
            'packages[insurance_type_id]' => $faker->numberBetween(1,2),
            'postmans[postman_id]' => 1,


        ]);*/
    }
}