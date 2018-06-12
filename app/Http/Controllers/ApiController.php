<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use Response;
use App\User;
use App\Module;
use App\InfusionsoftTag;
use Log;
use DB;
class ApiController extends Controller
{
    // Todo: Module reminder assigner


    private $contact;
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



    private function get_order($email){
        $infusionsoft = new InfusionsoftHelper();
        $contact=$infusionsoft->getContact($email);
        $this->contact=$contact;
        $order=NULL;
        try {
            $order=explode(",",$contact["_Products"]);
        } catch (\Exception $e) {
            Log::info("Exception getting order. Exception message -> ".$e->getMessage());
        }

        return $order;

    }

    private function get_user_completed($email){
        $query="
            SELECT
                m.name ,
                m.course_key
            FROM 
                modules m 
                JOIN user_completed_modules ucm on ucm.module_id=m.id
                JOIN users u on u.id=ucm.user_id
            WHERE 
                u.email='$email'

        ";
        return DB::select(DB::raw($query));
    }

    private function get_user_courses_count($get_user_completed){

        $courses=array_map(function($element){

            return $element->course_key;
        }, $get_user_completed);
        return array_count_values($courses);
    }

    private function get_user_module_count($get_user_completed,$course_key){
        

        $modules=array_map(function($element)use($course_key){

            if ($element->course_key==$course_key) {
                # code...
                return $element->name;
            }
        }, $get_user_completed);
        return count($modules);
    }
    private function get_tag($module_index,$course_key,$order=[]){
        $index=$module_index+1;
        $tag="--";
        switch ($course_key) {
            case 'all':
                # code...
                $tag="Module reminders completed";
                break;
            case 'none':
                # code...
                $tag="Start ".strtoupper($order[0])." Module 1 Reminders";
                break;
            default:
                $tag="Start ".strtoupper($course_key)." Module ".$index." Reminders";
                break;
        }
        return $tag;
    }

  
    private function active_course($get_user_courses_count,$order)
    {
        # code...
        $active_course="none";
      
        foreach ($order as $o) {
            # code...
            try {
                
                $value=$get_user_courses_count[$o];
                
                if ($value<7) {
                    //dump($value);
                    $active_course=$o;
                    break;
                }
            } catch (\Exception $e) {
                
            }

        }
        if (count($get_user_courses_count)>=7 and $active_course=="none") {
            # code...
            $active_course="all";
        }
        return $active_course;

    }
    /***************Handler************************/ 

    public function module_reminder_assigner($email="")
    {
        # code...
        $success=false;
        $message="";
        $ret=compact('success','message');
        //$email=$r->contact_email;
        try {
            $data=$this->get_user_completed($email);
            //return $data;
            $order=$this->get_order($email);

            $courses=$this->get_user_courses_count($data);
           
            $course_key=$this->active_course($courses,$order);
            
            $module_count=$this->get_user_module_count($data,$course_key);

            $tag=$this->get_tag($module_count,$course_key,$order);
            $tag_id=InfusionsoftTag::where("name",$tag)->value("id");
            $contact_id=$this->contact["Id"];
            
            $infusionsoft=new InfusionsoftHelper();
            $iResponse=$infusionsoft->addTag($contact_id, $tag_id);
            $ret["success"]=$iResponse;
            
        } catch (\Exception $e) {
            $ret["message"]=$e->getMessage();
        }
        return Response::json($ret); 
    }


    /***********************************************/ 

    public function boot()
    {
        $this->save_tags_locally();
        return "tags saved"   ;
    }

    /*************TESTS***************/
    public function test_get_order($email="5b1eddfa78d56@test.com")
    {
        $data=$this->get_user_completed($email);
        //return $data;
        $order=$this->get_order($email);

        $courses=$this->get_user_courses_count($data);
       
        $course_key=$this->active_course($courses,$order);
        
        $module_count=$this->get_user_module_count($data,$course_key);

        $tag=$this->get_tag($module_count,$course_key,$order);
        dump($tag);
        $tag_id=InfusionsoftTag::where("name",$tag)->value("id");
        $contact_id=$this->contact["Id"];
        dump($tag_id,$contact_id);
        $infusionsoft=new InfusionsoftHelper();
        $iResponse=$infusionsoft->addTag($contact_id, $tag_id);
        $ret["success"]=$iResponse;
        return Response::json($ret);
    }
}
