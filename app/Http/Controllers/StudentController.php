<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    // 1. GET: Retrieve all student records
    public function index()
    {
        return response()->json(Student::all(), 200);
    }

    // 2. GET: Retrieve one student by ID
    public function show(string $id)
    {
        $student = Student::findOrFail($id);

        return response()->json($student, 200);
    }

    // 3. POST: Create a new student record with input validation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:students,email',
            ],
            'course' => ['required', 'string', 'max:255'],
        ]);

        $student = Student::create($validated);

        return response()->json([
            'message' => 'Student created successfully.',
            'data' => $student,
        ], 201);
    }

    // 4. PUT: Update an existing student record with input validation
    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
            'course' => ['required', 'string', 'max:255'],
        ]);

        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully.',
            'data' => $student,
        ], 200);
    }

    // 5. DELETE: Remove an existing student record
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);

        $student->delete();

        return response()->json([
            'message' => 'Student deleted successfully.',
        ], 200);
    }
}
