<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use Response;
use App\User;
use App\Module;
use App\InfusionsoftTag;
use Log;

class ApiController extends Controller
{
    // Todo: Module reminder assigner



    private function exampleCustomer(){

        $infusionsoft = new InfusionsoftHelper();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }

    private function save_tags_locally(){
        $infusionsoft = new InfusionsoftHelper();
        $tags=$infusionsoft->getAllTags();
        $count=count($tags);
        $success=0;
        Log::info("Total tags received ".$count);
        for ($i=0; $i < $count; $i++) { 
            # code...
            $tag=$tags[$i];

            if (!empty($tag->name) && !empty($tag->id)) {
               try {
                InfusionsoftTag::create([
                'id'=>$tag->id,
                'name'=>$tag->name,
                'description'=>$tag->description,
                'category'=>$tag->category
                ]);
                $success++;
               } catch (\Exception $e) {
                   Log::info("Exception saving tag. Exception message -> ".$e->getMessage());
               }

            }
        }
        Log::info("Total tags saved ".$success);

    }

    public function boot()
    {
        $this->save_tags_locally();
        return "tags saved"   ;
    }
}
