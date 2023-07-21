<style>
.styled-table {
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    font-family: sans-serif;
    min-width: 400px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
}
.styled-table thead tr {
    background-color: #009879;
    color: #ffffff;
    text-align: left;
}
.styled-table th,
.styled-table td {
    padding: 12px 15px;
}
.styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid #009879;
}
.styled-table tbody tr.active-row {
    font-weight: bold;
    color: #009879;
}
</style>
<!-- create a form -->
<form method="GET" action="/wonde">
    @csrf
    <div>From Date:
    <select name="fromDate">
        @foreach($daysForSelection as $day)
            <option value="{{ $day }}" {{ ($previousFromDate ?? null) == $day ? 'selected="selected"' : '' }}>{{ $day }}</option>
        @endforeach
    </select>
    </div>
    <div>Employee: 
    <select name="employeeId">
        @if ($allEmployees)
        <option value="">Please select employee</option>
        @foreach($allEmployees as $employee)
            <option value="{{ $employee->id }}" {{ ($previousEmployeeId ?? null) == $employee->id ? 'selected="selected"' : '' }}>{{ $employee->title }} {{ $employee->forename }} {{ $employee->surname }}</option>
        @endforeach

    @endif
    </select>
    </div>
    <div><button type="submit">Submit</button></div>
</form>
@if(isset($dayOfWeek))
<table class="styled-table">

    <thead>
        <tr>
            <th>Date</th>
            <th>Period</th>
            <th>Class</th>
            <th>Students</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($dayOfWeek as $day => $detail)

        @foreach($detail as $slot)
        <tr>

            <td>{{ substr($slot['lesson']->start_at->date,0,10) }} {{ ucfirst($slot['period']->day) }}</td>

            <td>{{ $slot['period']->start_time }} - {{ $slot['period']->end_time }}</td>

            <td>{{ $slot['class']->name }}</td>

            <td>{{ implode(', ', array_map( fn($student) => $student->forename . " " . $student->surname ,$slot['students'] )) }}</td>

        <tr>
        @endforeach
    @endforeach
    </tbody>
<table>
@else
<h1> No Result Found </h1>
@endif