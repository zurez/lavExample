<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    private $url="/api/module_reminder_assigner";
    private $post_data=[
        "contact_email"=>"5b1eddfa78d56@test.com",
        "test"=>true
    ];
    public function testPass()
    {

        $response = $this->json('POST',$this->url,$this->post_data);

        $response->assertStatus(200)
        ->assertJson([
                'success' => true,
        ]);;
    }

    public function testFail()
    {
        # code...
        $response = $this->json('POST',$this->url,["test"=>true]);

        $response->assertStatus(200)
        ->assertJson([
                'success' => false,
        ]);;

    }

    public function testNoUser()
    {
        # code...
         $response = $this->json('POST',$this->url,["test"=>true,
            "contact_email"=>"123444"]);

        $response->assertStatus(200)
        ->assertJson([
                'success' => false,
        ]);;
    }
}
