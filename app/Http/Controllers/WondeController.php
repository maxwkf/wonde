<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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
        // 📢 with the assumption of employees, classes and student wont change frequently
        // , we can cache the results for a day


        $allEmployees = Cache::remember('allEmployees', $this->_cacheSeconds, function () {
            // get all employess for generation of selection box
            return $this->_school->employees->all();
        });

        $daysForSelection = array_map(function($dayCount) {
            // -7 is intensionally added
            // I am not sure if the testing school will add more lessons after today
            // so, historical from date selection can be useful for demonstration.
            return Carbon::now()->addDays($dayCount - 7)->format('Y-m-d');
        }, range(0,14));
        
        $basicResultSet = [
            'allEmployees' => $allEmployees,
            'previousEmployeeId' => request()->get('employeeId'),
            'daysForSelection' => $daysForSelection,
            'previousFromDate' => request()->get('fromDate'),
        ];
        // if no employeeId is set, return without results
        if (!request()->has('employeeId')) {
            return view('wonde', $basicResultSet);
        }

        // calculate a week period        
        $startDatetime = Carbon::createFromFormat('Y-m-d', request()->get('fromDate'))->format('Y-m-d 00:00:00');
        $endDatetime = Carbon::createFromFormat('Y-m-d', request()->get('fromDate'))->addDays(7)->format('Y-m-d 00:00:00');

        // retrieve lessons for that week
        // ❓ I can see that the start_at and end_at inside lesson is different from that of period start_time and end_time
        // Timezone handling maybe required
        $lessons = $this->_school->lessons->all(['class', 'period', 'employee'], ['lessons_start_after' => $startDatetime, 'lessons_start_before' => $endDatetime]);

        // filter lessons by employeeId
        // cannot use array_filter as lessons is ResultIterator object
        $lessonsTaughtByTeacher = [];
        foreach($lessons as $lesson) {
            // it is weird that the lesson does not contain employee_id that specified in https://docs.wonde.com/docs/api/sync#lesson-object

            if ($lesson->employee?->data->id == request()->get('employeeId'))
            $lessonsTaughtByTeacher[] = $lesson;
        }
        
        // putting lessons in format for output
        $resultSet = [];
        $count = 0;
        foreach($lessonsTaughtByTeacher as $lesson) {
            if ($lesson->period->data->day && $lesson->employee) {
                $count++;
                
                $resultSet['dayOfWeek'][$lesson->period->data->day][] = [
                    'lesson' => $lesson,
                    'period' => $lesson->period->data,
                    'employee' => $lesson->employee->data,
                    'class' => $lesson->class->data,
                    'students' => $this->_school->classes->get($lesson->class->data->id, ['students'])?->students?->data
                ];
            }
        }
        
        return view('wonde', array_merge($basicResultSet, $resultSet));
    }
}
