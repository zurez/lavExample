<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfusionsoftTag extends Model
{
    //
    protected $table="infusionsofttags";

    protected $fillable=[
    	"id",
    	"name",
    	"description",
    	"category"
    ];
}
