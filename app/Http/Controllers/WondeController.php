<?php

namespace App\Http\Controllers;

class WondeController extends Controller
{
    private $_client;
    private $_school;

    public function __construct(\Wonde\Client $client) {
        $this->_client = $client;
        $this->_school = $this->_client->school(env('WONDE_SCHOOL_ID'));
    }
    public function show() {

        // get all employess for generation of selection box
        $allEmployees = $this->_school->employees->all();

        // if no employeeId is set, return without results
        if (!request()->has('employeeId')) {
            return view('wonde', [
                'allEmployees' => $allEmployees
            ]);
        }

        // get employee, A500460806
        $targetEmployee = $this->_school->employees->get(request()->get('employeeId'), ['classes']);
        
        // get classes from employee
        $classes = array_reduce($targetEmployee->classes->data, function($carry, $class) {
            $carry[] = $this->_school->classes->get($class->id, ['students']);
            return $carry;
        }, []);

        return view('wonde', [
            'allEmployees' => $allEmployees,
            'targetEmployee' => $targetEmployee,
            'classes' => $classes
        ]);
    }

    
}
