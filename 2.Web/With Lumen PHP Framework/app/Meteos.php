<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Meteos extends Model
{
     protected $fillable = ['vitesse', 'direction', 'temperature'];
     public $timestamps = true;
}
