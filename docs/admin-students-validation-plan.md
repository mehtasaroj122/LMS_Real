# Admin Students Validation Plan

## Scope

This plan covers the Admin Students add and edit modals in:

- `resources/views/Admin/Students.blade.php`
- `app/Http/Controllers/Admin/StudentController.php`

`date_of_birth` is stored on the `users` table and exposed through the student modals.

## Validation Rules

### Frontend and Backend Shared Rules

- `name`
  - Required
  - Minimum 2 characters
  - Maximum 100 characters
  - Letters and spaces only
- `email`
  - Required
  - Must be valid email format
  - Maximum 255 characters
  - Must be unique in `users.email`
- `phone`
  - Required
  - Must include country code
  - Must match international format like `+9779812345678`
  - Must be unique in `users.phone`
- `date_of_birth`
  - Required
  - Must be a valid date
  - Must be earlier than today
  - Student age must be between 14 and 100
- `roll_no`
  - Required
  - Used as the student ID in the admin form
  - Minimum 3 characters
  - Maximum 30 characters
  - Letters, numbers, and hyphens only
  - Must be unique in `students.roll_no`
- `department_id`
  - Required
  - Must exist in `departments.id`
- `batch`
  - Required
  - Must be a 4-digit year
- `semester`
  - Required
  - Must be an integer between 1 and 12
- `address`
  - Required
  - Minimum 10 characters
  - Maximum 255 characters
  - Rejects HTML or script-like content
- `status`
  - Required on edit
  - Must be `active` or `inactive`

## Error Messages

### Exact Message Wording

- `name.required`: `Enter the student's full name.`
- `name.min`: `Full name must be at least 2 characters long.`
- `name.regex`: `Full name can use letters and spaces only.`
- `email.required`: `Enter the student's email address.`
- `email.email`: `Enter a valid email address, like student@example.com.`
- `email.unique`: `This email is already assigned to another user.`
- `phone.required`: `Enter the student's phone number with country code.`
- `phone.regex`: `Enter a valid phone number with country code, like +9779812345678.`
- `phone.unique`: `This phone number is already assigned to another user.`
- `date_of_birth.required`: `Select the student's date of birth.`
- `date_of_birth.date`: `Enter a valid date of birth.`
- `date_of_birth.before`: `Date of birth must be earlier than today.`
- `date_of_birth.age`: `Student age must be between 14 and 100 years.`
- `roll_no.required`: `Enter the student ID.`
- `roll_no.min`: `Student ID must be at least 3 characters long.`
- `roll_no.regex`: `Student ID can use letters, numbers, and hyphens only.`
- `roll_no.unique`: `This student ID is already in use.`
- `department_id.required`: `Select a department.`
- `batch.required`: `Enter the batch year.`
- `batch.regex`: `Batch year must be a 4-digit year.`
- `semester.required`: `Enter the semester number.`
- `semester.between`: `Semester must be a number between 1 and 12.`
- `address.required`: `Enter the student's address.`
- `address.min`: `Address must be at least 10 characters long.`
- `address.not_regex`: `Address contains unsupported characters. Remove any HTML or script-like content.`
- `status.required`: `Select the student status.`

## Validation Flow

### Frontend

- On modal open:
  - Add form resets clean and submit stays disabled until every field is valid.
  - Edit form loads server values, validates them, and enables submit only if the loaded state is valid.
- On input or change:
  - The top validation summary is cleared immediately.
  - The changed field is revalidated locally.
  - Email, phone, and student ID lose their verified state when edited.
- On blur:
  - The field is revalidated.
  - Email, phone, and student ID run async uniqueness checks against `/admin/students/validate-field`.
- On submit:
  - Every field is validated.
  - Each invalid field shows its own message directly below the control.
  - Submission stops until every field passes.

### Backend

- All incoming values are normalized first:
  - Trim whitespace
  - Collapse repeated spaces
  - Lowercase email
  - Uppercase student ID
  - Strip HTML from text inputs
- Store and update both use the same validation rules/messages.
- Unique constraints are verified in validation and rechecked through database exception handling for race conditions.

## Error Communication

- Frontend async validation receives JSON with:
  - `valid`
  - `field`
  - `message`
- Store and update return Laravel validation JSON with:
  - `errors[field]`
- Frontend maps backend errors into:
  - invalid field state
  - one visible summary message using the first backend error

## Visual Feedback

- Valid fields:
  - green border
  - green check icon
- Invalid fields:
  - red border
  - red warning icon
- Pending uniqueness checks:
  - blue border
  - spinner icon
- Field errors:
  - shown directly below the affected field
  - paired with red borders and warning icons

## Accessibility

- Invalid fields receive `aria-invalid="true"`.
- Each invalid field keeps its own inline error text for better context.

## Testing Strategy

### Valid Cases

- Add a student with a valid name, unique email, unique phone, valid DOB, valid student ID, department, batch, semester, and address.
- Edit a student without changing unique fields.
- Edit a student and change email, phone, or student ID to a new unique value.

### Boundary Cases

- Name with exactly 2 characters
- Student ID with exactly 3 characters
- Address with exactly 10 characters
- Age exactly 14
- Age exactly 100
- Semester `1` and `12`
- Batch matching a valid 4-digit year

### Invalid Cases

- Empty required fields
- Name with digits or symbols
- Invalid email format
- Phone without country code
- Future DOB
- Underage or overage DOB
- Duplicate email
- Duplicate phone
- Duplicate student ID
- Invalid semester
- Address shorter than 10 characters

### Security Cases

- Address containing `<script>`
- Name with injected HTML
- Oversized field values
- Malformed JSON payloads on update

## Operational Note

`date_of_birth` now lives in the original `users` table migration for fresh setups.
