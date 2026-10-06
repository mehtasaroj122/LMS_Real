    <div id="{{ $studentOnly ? 'addStudentModal' : 'addUserModal' }}" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">{{ $studentOnly ? 'Add New Student' : 'Add New User' }}</h3>
                <button type="button" class="modal-close-btn" id="{{ $studentOnly ? 'closeAddStudentModal' : 'closeAddUserModal' }}" aria-label="Close add dialog">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Create an invited user account. Staff and students will finish registration themselves and set their own password.</p>

                <form id="{{ $studentOnly ? 'addStudentForm' : 'addUserForm' }}" novalidate>
                    @csrf

                    <div class="form-group">
                        <label class="form-label required">Full Name</label>
                        <input type="text" class="form-control" name="name" id="addName" placeholder="John Doe">
                        <span class="field-error" id="addNameError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" id="addEmail" placeholder="john@example.com">
                        <span class="field-error" id="addEmailError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Role</label>
                        <select class="form-control" name="role" id="addRoleSelect" required>
                            @if (!$studentOnly)
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            @endif
                            <option value="student" selected>Student</option>
                        </select>
                        <span class="field-error" id="addRoleError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone" id="addPhone" placeholder="+9779812345678">
                        <span class="field-error" id="addPhoneError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-control" name="gender" id="addGender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="field-error" id="addGenderError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" id="addDateOfBirthLabel" for="addDateOfBirth">Date of Birth<span class="required-star" data-student-required> *</span></label>
                        <input type="date" class="form-control" name="date_of_birth" id="addDateOfBirth" max="{{ today()->subDay()->format('Y-m-d') }}">
                        <span class="field-error" id="addDateOfBirthError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff,student">
                        <label class="form-label required">Department</label>
                        <select class="form-control" name="department_id" id="addDepartmentSelect">
                            <option value="">Select Department</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="field-error" id="addDepartmentError"></span>
                    </div>

                    @if (!$studentOnly)
                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label required">Staff ID</label>
                        <input type="text" class="form-control" name="staff_id" id="addStaffId" placeholder="STAFF-000001">
                        <span class="field-error" id="addStaffIdError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label required">Staff Designation</label>
                        <input type="text" class="form-control" name="designation" id="addDesignation" placeholder="Staff Member">
                        <span class="field-error" id="addDesignationError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" name="join_date" id="addJoinDate">
                        <span class="field-error" id="addJoinDateError"></span>
                    </div>

                    @endif

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label required" id="rollNoLabel">Student ID</label>
                        <input type="text" class="form-control" name="roll_no" id="addRollNo" placeholder="CSE-2021-001">
                        <span class="field-error" id="addRollNoError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label required">Batch</label>
                        <input type="text" class="form-control" name="batch" id="addBatch" placeholder="2024">
                        <span class="field-error" id="addBatchError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label required">Semester</label>
                        <select class="form-control" name="semester" id="addSemester">
                            <option value="">Select Semester</option>
                            <option value="1">1st</option>
                            <option value="2">2nd</option>
                            <option value="3">3rd</option>
                            <option value="4">4th</option>
                            <option value="5">5th</option>
                            <option value="6">6th</option>
                            <option value="7">7th</option>
                            <option value="8">8th</option>
                        </select>
                        <span class="field-error" id="addSemesterError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="addAddress" placeholder="123 Main St, City, Country" rows="3"></textarea>
                        <span class="field-error" id="addAddressError"></span>
                    </div>

                    <div class="form-group" id="addStatusGroup">
                        <label class="form-label">Status</label>
                        <div class="status-radio" id="addStatusRadio">
                            <label class="status-option">
                                <input type="radio" name="status" value="active" id="addStatusActiveRadio">
                                <span>Active</span>
                            </label>
                            <label class="status-option">
                                <input type="radio" name="status" value="inactive" checked>
                                <span>Inactive</span>
                            </label>
                        </div>
                        <span class="field-error" id="addStatusError"></span>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="{{ $studentOnly ? 'cancelAddStudent' : 'cancelAddUser' }}">Cancel</button>
                <button type="submit" form="{{ $studentOnly ? 'addStudentForm' : 'addUserForm' }}" class="btn btn-primary" id="{{ $studentOnly ? 'submitAddStudent' : 'submitAddUser' }}">
                    <i class="fas fa-user-plus"></i>
                    {{ $studentOnly ? 'Add Student' : 'Add User' }}
                </button>
            </div>
        </div>
    </div>
