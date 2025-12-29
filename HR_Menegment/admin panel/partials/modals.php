<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" placeholder="John Doe">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" placeholder="john@company.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select class="form-select">
                            <option>HR</option><option>Engineering</option><option>Design</option><option>QA</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role / Title</label>
                        <input type="text" class="form-control" placeholder="HR Manager">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Active</option><option>Probation</option><option>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="2" placeholder="Add onboarding notes"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save Employee</button>
            </div>
        </div>
    </div>
</div>

<!-- Salary Modal -->
<div class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add / Update Salary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select class="form-select">
                        <option>Priya Sharma</option>
                        <option>Arjun Patel</option>
                        <option>Neha Verma</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Basic</label>
                        <input type="number" class="form-control" placeholder="50000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Allowances</label>
                        <input type="number" class="form-control" placeholder="12000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Deductions</label>
                        <input type="number" class="form-control" placeholder="2000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pay Date</label>
                        <input type="date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary">Save Salary</button>
            </div>
        </div>
    </div>
</div>

<!-- Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Project Name</label>
                        <input type="text" class="form-control" placeholder="Alpha Revamp">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select">
                            <option>Design</option><option>Development</option><option>QA</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Running</option><option>Paused</option><option>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assign Employees</label>
                        <select class="form-select" multiple size="4">
                            <option>Priya Sharma</option>
                            <option>Arjun Patel</option>
                            <option>Neha Verma</option>
                            <option>John Smith</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" placeholder="Project scope, milestones, delivery"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save Project</button>
            </div>
        </div>
    </div>
</div>

<!-- Documents Modal -->
<div class="modal fade" id="docsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Documents</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select class="form-select">
                        <option>Priya Sharma</option><option>Arjun Patel</option><option>Neha Verma</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Document Name</label>
                    <input type="text" class="form-control" placeholder="Offer Letter / ID Proof">
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload File</label>
                    <input type="file" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary">Save Document</button>
            </div>
        </div>
    </div>
</div>

