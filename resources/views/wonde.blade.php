<!-- create a form -->
<form method="GET" action="/wonde">
    @csrf
    Employee: 
    <select name="employeeId">
        @if ($allEmployees)

        @foreach($allEmployees as $employee)
            <option value="{{ $employee->id }}" {{ $targetEmployee == $employee->id ? 'selected="selected"' : '' }}>{{ $employee->title }} {{ $employee->forename }} {{ $employee->surname }}</option>
        @endforeach

    @endif
    </select>
    <button type="submit">Submit</button>
</form>
@isset($targetEmployee)
    @if($classes)
        @foreach ($classes as $class)
            <h1>Class: {{ $class->name }}</h1>
            @if($class->students->data)
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($class->students->data as $student)
                            <tr>
                                <td>{{ $student->forename }} {{ $student->surname }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <h1>No students found</h1>
            @endif
        @endforeach
    @else
        <h1>No classes found</h1>
    @endif
@endisset