<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;

class WondeController extends Controller
{
    private $_client;
    private $_school;
    private $_cacheDays;
    private $_cacheSeconds;

    public function __construct(\Wonde\Client $client) {
        $this->_client = $client;
        $this->_school = $this->_client->school(env('WONDE_SCHOOL_ID'));
        $this->_cacheDays = 1;
        $this->_cacheSeconds = 60 * 60 * 24 * $this->_cacheDays;
    }
    public function show() {
        // with the assumption of employees, classes and student wont change frequently
        // , we can cache the results for a day
        $allEmployees = Cache::remember('allEmployees', $this->_cacheSeconds, function () {
            // get all employess for generation of selection box
            return $this->_school->employees->all();
        });

        // if no employeeId is set, return without results
        if (!request()->has('employeeId')) {
            return view('wonde', [
                'allEmployees' => $allEmployees
            ]);
        }

        $targetEmployee = Cache::remember('targetEmployee_'. request()->get('employeeId'), $this->_cacheSeconds, function () {

            // get employee, A500460806
            return $this->_school->employees->get(request()->get('employeeId'), ['classes']);
        });

        // get classes from employee
        $classes = Cache::remember('classes_'. request()->get('employeeId'), $this->_cacheSeconds, function () use ($targetEmployee) {
            return array_reduce($targetEmployee->classes->data, function($carry, $class) {
                $carry[] = $this->_school->classes->get($class->id, ['students']);
                return $carry;
            }, []);
        });

        return view('wonde', [
            'allEmployees' => $allEmployees,
            'targetEmployee' => $targetEmployee,
            'classes' => $classes
        ]);
    }

    
}
