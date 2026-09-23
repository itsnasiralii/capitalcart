<div class="py-2">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="font-poppins fw-bold mb-1">Portfolio CMS &amp; Personal Brand</h4>
            <p class="text-muted small mb-0">Manage Nasir Ali's portfolio profile, projects, work experience, skills, and visitor messages.</p>
        </div>
        <a href="{{ route('portfolio') }}" target="_blank" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            View Live Portfolio
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}" wire:click="$set('activeTab', 'profile')">
                👤 Profile &amp; Bio
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'projects' ? 'active' : '' }}" wire:click="$set('activeTab', 'projects')">
                🚀 Projects ({{ $projectsList->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'experience' ? 'active' : '' }}" wire:click="$set('activeTab', 'experience')">
                ⏱️ Experience ({{ $experiencesList->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'skills' ? 'active' : '' }}" wire:click="$set('activeTab', 'skills')">
                🛠️ Skills ({{ $skillsList->count() }})
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'messages' ? 'active' : '' }}" wire:click="$set('activeTab', 'messages')">
                📬 Inbox
                @if($unreadCount > 0)
                    <span class="badge bg-danger ms-1">{{ $unreadCount }}</span>
                @endif
            </button>
        </li>
    </ul>

    {{-- ========================================================================= --}}
    {{-- TAB 1: PROFILE & BIO --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'profile')
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <form wire:submit.prevent="saveProfile">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">Full Name</label>
                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Professional Title</label>
                        <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-600">Professional Introduction (Hero)</label>
                        <textarea wire:model="intro" rows="3" class="form-control @error('intro') is-invalid @enderror"></textarea>
                        @error('intro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-600">Detailed About / Technical Philosophy</label>
                        <textarea wire:model="about" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Availability Status Badge</label>
                        <input type="text" wire:model="availability" class="form-control" placeholder="e.g. Available for Network Engineering & Software Solutions">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Primary Contact Email</label>
                        <input type="email" wire:model="email" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-600">WhatsApp Number</label>
                        <input type="text" wire:model="whatsapp" class="form-control" placeholder="03002922584">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-600">LinkedIn Profile URL</label>
                        <input type="url" wire:model="linkedin" class="form-control" placeholder="https://linkedin.com/in/...">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-600">GitHub Profile URL</label>
                        <input type="url" wire:model="github" class="form-control" placeholder="https://github.com/...">
                    </div>

                    {{-- Profile Photo Upload --}}
                    <div class="col-md-6 pt-3 border-top">
                        <label class="form-label fw-600">Profile Photograph</label>
                        @if($currentAvatar)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $currentAvatar) }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 64px; height: 64px; object-fit: cover;">
                                <span class="text-muted small ms-2">Current photo active</span>
                            </div>
                        @endif
                        <input type="file" wire:model="newAvatar" class="form-control" accept="image/*">
                        <div class="form-text small">Accepted formats: JPG, PNG, WEBP (Max 5MB).</div>
                    </div>

                    {{-- CV File Upload --}}
                    <div class="col-md-6 pt-3 border-top">
                        <label class="form-label fw-600">Curriculum Vitae (CV / Resume File)</label>
                        @if($currentCv)
                            <div class="mb-2 text-success small fw-semibold">
                                ✓ Custom CV file uploaded ({{ basename($currentCv) }})
                            </div>
                        @else
                            <div class="mb-2 text-muted small">
                                ℹ️ Default Nasir Ali CV summary will be served until a PDF is uploaded.
                            </div>
                        @endif
                        <input type="file" wire:model="newCv" class="form-control" accept=".pdf,.doc,.docx">
                        <div class="form-text small">Upload PDF or DOCX file (Max 10MB).</div>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold" wire:loading.attr="disabled">
                            Save Profile Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: PROJECTS --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'projects')
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Portfolio Projects</h5>
                <button type="button" wire:click="openProjectModal" class="btn btn-primary btn-sm">
                    + Add New Project
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Tech Stack</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projectsList as $proj)
                            <tr>
                                <td class="fw-bold text-muted" style="width: 60px;">#{{ $proj->sort_order }}</td>
                                <td>
                                    <div class="fw-600 text-dark">{{ $proj->title }}</div>
                                    <div class="text-muted small" style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $proj->description }}
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $proj->category }}</span></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 200px;">
                                        @foreach(array_slice($proj->tech_stack ?? [], 0, 3) as $t)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary small" style="font-size:0.7rem">{{ $t }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <button type="button" wire:click="toggleProjectStatus({{ $proj->id }})" class="btn btn-sm {{ $proj->is_active ? 'btn-success' : 'btn-secondary' }}" style="font-size:0.75rem; padding: 2px 8px;">
                                        {{ $proj->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </td>
                                <td class="text-end">
                                    <button type="button" wire:click="openProjectModal({{ $proj->id }})" class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                    <button type="button" wire:click="deleteProject({{ $proj->id }})" wire:confirm="Are you sure you want to delete this project?" class="btn btn-sm btn-outline-danger">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No projects found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Project Modal --}}
        @if($showProjectModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">{{ $editingProjectId ? 'Edit Project' : 'Add New Project' }}</h5>
                            <button type="button" class="btn-close" wire:click="$set('showProjectModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-600">Project Title <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="projTitle" class="form-control @error('projTitle') is-invalid @enderror">
                                    @error('projTitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
                                    <select wire:model="projCategory" class="form-select">
                                        <option value="Network automation">Network automation</option>
                                        <option value="CNOC operational tools">CNOC operational tools</option>
                                        <option value="Network troubleshooting">Network troubleshooting</option>
                                        <option value="E-commerce development">E-commerce development</option>
                                        <option value="Python and Gradio applications">Python and Gradio applications</option>
                                        <option value="Reporting and workflow automation">Reporting and workflow automation</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Description <span class="text-danger">*</span></label>
                                    <textarea wire:model="projDescription" rows="3" class="form-control @error('projDescription') is-invalid @enderror"></textarea>
                                    @error('projDescription') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Tech Stack Tags (Comma separated)</label>
                                    <input type="text" wire:model="projTechStack" class="form-control" placeholder="e.g. Python, Netmiko, SSH, Linux">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Live Demo URL</label>
                                    <input type="url" wire:model="projDemoUrl" class="form-control" placeholder="https://...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">GitHub Repository URL</label>
                                    <input type="url" wire:model="projGithubUrl" class="form-control" placeholder="https://github.com/...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Display Sort Order</label>
                                    <input type="number" wire:model="projSortOrder" class="form-control">
                                </div>
                                <div class="col-md-6 d-flex align-items-center pt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="projIsActive" id="projActiveCheck">
                                        <label class="form-check-label fw-600" for="projActiveCheck">Visible on public portfolio</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showProjectModal', false)">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="saveProject">Save Project</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 3: EXPERIENCE TIMELINE --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'experience')
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Experience Timeline</h5>
                <button type="button" wire:click="openExperienceModal" class="btn btn-primary btn-sm">
                    + Add Work Experience
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Role &amp; Company</th>
                            <th>Period</th>
                            <th>Responsibilities</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($experiencesList as $exp)
                            <tr>
                                <td class="fw-bold text-muted" style="width: 60px;">#{{ $exp->sort_order }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $exp->role }}</div>
                                    <div class="text-primary small fw-semibold">{{ $exp->company }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $exp->period }}</span></td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        {{ count($exp->responsibilities ?? []) }} points
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" wire:click="openExperienceModal({{ $exp->id }})" class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                    <button type="button" wire:click="deleteExperience({{ $exp->id }})" wire:confirm="Delete this experience record?" class="btn btn-sm btn-outline-danger">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No experience entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Experience Modal --}}
        @if($showExperienceModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">{{ $editingExperienceId ? 'Edit Experience' : 'Add Experience' }}</h5>
                            <button type="button" class="btn-close" wire:click="$set('showExperienceModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Role / Designation <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="expRole" class="form-control @error('expRole') is-invalid @enderror" placeholder="e.g. Corporate NOC Engineer">
                                    @error('expRole') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="expCompany" class="form-control @error('expCompany') is-invalid @enderror" placeholder="e.g. Zong CMPak">
                                    @error('expCompany') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Period / Duration <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="expPeriod" class="form-control @error('expPeriod') is-invalid @enderror" placeholder="e.g. January 2026 – Present">
                                    @error('expPeriod') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-600">Sort Order</label>
                                    <input type="number" wire:model="expSortOrder" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Summary Description</label>
                                    <textarea wire:model="expDescription" rows="2" class="form-control"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Key Responsibilities (One bullet point per line)</label>
                                    <textarea wire:model="expResponsibilities" rows="5" class="form-control" placeholder="Monitor and support enterprise network services&#10;Troubleshoot complex connectivity incidents..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showExperienceModal', false)">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="saveExperience">Save Experience</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 4: SKILLS --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'skills')
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Technical Skills Categories</h5>
                <button type="button" wire:click="openSkillModal" class="btn btn-primary btn-sm">
                    + Add Skill Category
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Category</th>
                            <th>Skills List</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($skillsList as $sk)
                            <tr>
                                <td class="fw-bold text-muted" style="width: 60px;">#{{ $sk->sort_order }}</td>
                                <td class="fw-bold text-dark">{{ $sk->category }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($sk->skills ?? [] as $item)
                                            <span class="badge bg-light text-dark border">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button type="button" wire:click="openSkillModal({{ $sk->id }})" class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                    <button type="button" wire:click="deleteSkill({{ $sk->id }})" wire:confirm="Delete this skill category?" class="btn btn-sm btn-outline-danger">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No skill categories configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Skill Modal --}}
        @if($showSkillModal)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">{{ $editingSkillId ? 'Edit Skill Category' : 'Add Skill Category' }}</h5>
                            <button type="button" class="btn-close" wire:click="$set('showSkillModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-600">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="skillCategory" class="form-control @error('skillCategory') is-invalid @enderror" placeholder="e.g. Networking / Telecom">
                                    @error('skillCategory') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Skills (Comma separated) <span class="text-danger">*</span></label>
                                    <textarea wire:model="skillItems" rows="3" class="form-control @error('skillItems') is-invalid @enderror" placeholder="TCP/IP, BGP, OSPF, VLANs, DNS, IPv6"></textarea>
                                    @error('skillItems') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-600">Sort Order</label>
                                    <input type="number" wire:model="skillSortOrder" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showSkillModal', false)">Cancel</button>
                            <button type="button" class="btn btn-primary" wire:click="saveSkill">Save Skills</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 5: INBOX MESSAGES --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'messages')
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <h5 class="fw-bold mb-3">Transmitted Visitor Inquiries</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Status</th>
                            <th>Sender</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messagesList as $msg)
                            <tr class="{{ !$msg->is_read ? 'table-warning' : '' }}">
                                <td>
                                    @if(!$msg->is_read)
                                        <span class="badge bg-warning text-dark">Unread</span>
                                    @else
                                        <span class="badge bg-light text-muted border">Read</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $msg->name }}</div>
                                    <div class="text-muted small">{{ $msg->email }}</div>
                                </td>
                                <td>
                                    <div class="fw-600 text-dark">{{ $msg->subject }}</div>
                                    <div class="text-muted small" style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $msg->message }}
                                    </div>
                                </td>
                                <td class="text-muted small">{{ $msg->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-end">
                                    <button type="button" wire:click="viewMessage({{ $msg->id }})" class="btn btn-sm btn-outline-primary me-1">View</button>
                                    <button type="button" wire:click="deleteMessage({{ $msg->id }})" wire:confirm="Delete this message?" class="btn btn-sm btn-outline-danger">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No messages received yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $messagesList->links() }}
            </div>
        </div>

        {{-- View Message Modal --}}
        @if($viewingMessage)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">{{ $viewingMessage->subject }}</h5>
                            <button type="button" class="btn-close" wire:click="closeMessageModal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-between mb-3 text-muted small border-bottom pb-2">
                                <div>From: <strong>{{ $viewingMessage->name }}</strong> ({{ $viewingMessage->email }})</div>
                                <div>{{ $viewingMessage->created_at->format('M d, Y g:i A') }}</div>
                            </div>
                            <div class="p-3 bg-light rounded" style="white-space: pre-wrap; line-height: 1.6;">
                                {{ $viewingMessage->message }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="mailto:{{ $viewingMessage->email }}?subject={{ urlencode('Re: ' . $viewingMessage->subject) }}" class="btn btn-primary btn-sm">
                                Reply via Email
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="closeMessageModal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
