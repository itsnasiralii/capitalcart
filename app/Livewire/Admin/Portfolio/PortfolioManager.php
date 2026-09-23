<?php

namespace App\Livewire\Admin\Portfolio;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\PortfolioProject;
use App\Models\PortfolioExperience;
use App\Models\PortfolioSkill;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PortfolioManager extends Component
{
    use WithFileUploads, WithPagination;

    public string $activeTab = 'profile'; // profile, projects, experience, skills, messages

    // Profile Settings
    public string $name = '';
    public string $title = '';
    public string $intro = '';
    public string $about = '';
    public string $availability = '';
    public string $email = '';
    public string $phone = '';
    public string $whatsapp = '';
    public string $linkedin = '';
    public string $github = '';
    public string $instagram = '';
    public $newAvatar = null;
    public $newCv = null;
    public ?string $currentAvatar = null;
    public ?string $currentCv = null;

    // Project Form State
    public bool $showProjectModal = false;
    public ?int $editingProjectId = null;
    public string $projTitle = '';
    public string $projCategory = '';
    public string $projDescription = '';
    public string $projTechStack = '';
    public string $projDemoUrl = '';
    public string $projGithubUrl = '';
    public int $projSortOrder = 0;
    public bool $projIsActive = true;
    public $projImage = null;

    // Experience Form State
    public bool $showExperienceModal = false;
    public ?int $editingExperienceId = null;
    public string $expRole = '';
    public string $expCompany = '';
    public string $expPeriod = '';
    public string $expDescription = '';
    public string $expResponsibilities = ''; // Line by line
    public int $expSortOrder = 0;
    public bool $expIsActive = true;

    // Skill Form State
    public bool $showSkillModal = false;
    public ?int $editingSkillId = null;
    public string $skillCategory = '';
    public string $skillItems = ''; // Comma separated
    public int $skillSortOrder = 0;
    public bool $skillIsActive = true;

    // Message viewing
    public ?ContactMessage $viewingMessage = null;

    public function mount(): void
    {
        $this->loadProfileSettings();
    }

    public function loadProfileSettings(): void
    {
        $this->name         = setting('portfolio_name', 'Nasir Ali');
        $this->title        = setting('portfolio_title', 'Network Engineer & Software Developer');
        $this->intro        = setting('portfolio_intro', '');
        $this->about        = setting('portfolio_about', '');
        $this->availability = setting('portfolio_availability', '');
        $this->email        = setting('portfolio_email', '');
        $this->phone        = setting('portfolio_phone', '');
        $this->whatsapp     = setting('portfolio_whatsapp', '');
        $this->linkedin     = setting('portfolio_linkedin', '');
        $this->github       = setting('portfolio_github', '');
        $this->instagram    = setting('portfolio_instagram', '');
        $this->currentAvatar = setting('portfolio_avatar');
        $this->currentCv    = setting('portfolio_cv_file');
    }

    public function saveProfile(): void
    {
        $this->validate([
            'name'      => 'required|string|max:100',
            'title'     => 'required|string|max:150',
            'intro'     => 'required|string|max:2000',
            'email'     => 'nullable|email|max:150',
            'newAvatar' => 'nullable|image|max:5120', // 5MB max
            'newCv'     => 'nullable|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        $settings = [
            'portfolio_name'         => $this->name,
            'portfolio_title'        => $this->title,
            'portfolio_intro'        => $this->intro,
            'portfolio_about'        => $this->about,
            'portfolio_availability' => $this->availability,
            'portfolio_email'        => $this->email,
            'portfolio_phone'        => $this->phone,
            'portfolio_whatsapp'     => $this->whatsapp,
            'portfolio_linkedin'     => $this->linkedin,
            'portfolio_github'       => $this->github,
            'portfolio_instagram'    => $this->instagram,
        ];

        // Avatar Upload
        if ($this->newAvatar) {
            $avatarPath = $this->newAvatar->store('portfolio', 'public');
            $settings['portfolio_avatar'] = $avatarPath;
            $this->currentAvatar = $avatarPath;
            $this->newAvatar = null;
        }

        // CV Upload
        if ($this->newCv) {
            $cvPath = $this->newCv->store('portfolio/cv', 'public');
            $settings['portfolio_cv_file'] = $cvPath;
            $this->currentCv = $cvPath;
            $this->newCv = null;
        }

        foreach ($settings as $key => $value) {
            \App\Models\Setting::set($key, $value, 'portfolio');
        }

        session()->flash('success', 'Profile and portfolio settings updated successfully!');
    }

    // =========================================================================
    // Projects CRUD
    // =========================================================================
    public function openProjectModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->editingProjectId = $id;

        if ($id) {
            $proj = PortfolioProject::findOrFail($id);
            $this->projTitle       = $proj->title;
            $this->projCategory    = $proj->category;
            $this->projDescription = $proj->description;
            $this->projTechStack   = implode(', ', $proj->tech_stack ?? []);
            $this->projDemoUrl     = $proj->demo_url ?? '';
            $this->projGithubUrl   = $proj->github_url ?? '';
            $this->projSortOrder   = $proj->sort_order;
            $this->projIsActive    = $proj->is_active;
        } else {
            $this->projTitle       = '';
            $this->projCategory    = 'Network automation';
            $this->projDescription = '';
            $this->projTechStack   = '';
            $this->projDemoUrl     = '';
            $this->projGithubUrl   = '';
            $this->projSortOrder   = PortfolioProject::count() + 1;
            $this->projIsActive    = true;
        }

        $this->projImage = null;
        $this->showProjectModal = true;
    }

    public function saveProject(): void
    {
        $this->validate([
            'projTitle'       => 'required|string|max:150',
            'projCategory'    => 'required|string|max:100',
            'projDescription' => 'required|string|max:3000',
            'projImage'       => 'nullable|image|max:5120',
        ]);

        $techStack = array_values(array_filter(array_map('trim', explode(',', $this->projTechStack))));

        $data = [
            'title'       => $this->projTitle,
            'slug'        => Str::slug($this->projTitle) . '-' . ($this->editingProjectId ?: time()),
            'category'    => $this->projCategory,
            'description' => $this->projDescription,
            'tech_stack'  => $techStack,
            'demo_url'    => $this->projDemoUrl ?: null,
            'github_url'  => $this->projGithubUrl ?: null,
            'sort_order'  => $this->projSortOrder,
            'is_active'   => $this->projIsActive,
        ];

        if ($this->projImage) {
            $data['image'] = $this->projImage->store('portfolio/projects', 'public');
        }

        if ($this->editingProjectId) {
            PortfolioProject::findOrFail($this->editingProjectId)->update($data);
            session()->flash('success', 'Project updated successfully!');
        } else {
            PortfolioProject::create($data);
            session()->flash('success', 'Project created successfully!');
        }

        $this->showProjectModal = false;
    }

    public function toggleProjectStatus(int $id): void
    {
        $proj = PortfolioProject::findOrFail($id);
        $proj->is_active = !$proj->is_active;
        $proj->save();
    }

    public function deleteProject(int $id): void
    {
        PortfolioProject::findOrFail($id)->delete();
        session()->flash('success', 'Project removed.');
    }

    // =========================================================================
    // Experience CRUD
    // =========================================================================
    public function openExperienceModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->editingExperienceId = $id;

        if ($id) {
            $exp = PortfolioExperience::findOrFail($id);
            $this->expRole             = $exp->role;
            $this->expCompany          = $exp->company;
            $this->expPeriod           = $exp->period;
            $this->expDescription      = $exp->description ?? '';
            $this->expResponsibilities = implode("\n", $exp->responsibilities ?? []);
            $this->expSortOrder        = $exp->sort_order;
            $this->expIsActive         = $exp->is_active;
        } else {
            $this->expRole             = '';
            $this->expCompany          = '';
            $this->expPeriod           = '';
            $this->expDescription      = '';
            $this->expResponsibilities = '';
            $this->expSortOrder        = PortfolioExperience::count() + 1;
            $this->expIsActive         = true;
        }

        $this->showExperienceModal = true;
    }

    public function saveExperience(): void
    {
        $this->validate([
            'expRole'    => 'required|string|max:150',
            'expCompany' => 'required|string|max:150',
            'expPeriod'  => 'required|string|max:100',
        ]);

        $responsibilities = array_values(array_filter(array_map('trim', explode("\n", $this->expResponsibilities))));

        $data = [
            'role'             => $this->expRole,
            'company'          => $this->expCompany,
            'period'           => $this->expPeriod,
            'description'      => $this->expDescription ?: null,
            'responsibilities' => $responsibilities,
            'sort_order'       => $this->expSortOrder,
            'is_active'        => $this->expIsActive,
        ];

        if ($this->editingExperienceId) {
            PortfolioExperience::findOrFail($this->editingExperienceId)->update($data);
            session()->flash('success', 'Experience record updated!');
        } else {
            PortfolioExperience::create($data);
            session()->flash('success', 'Experience record created!');
        }

        $this->showExperienceModal = false;
    }

    public function deleteExperience(int $id): void
    {
        PortfolioExperience::findOrFail($id)->delete();
        session()->flash('success', 'Experience deleted.');
    }

    // =========================================================================
    // Skills CRUD
    // =========================================================================
    public function openSkillModal(?int $id = null): void
    {
        $this->resetErrorBag();
        $this->editingSkillId = $id;

        if ($id) {
            $skill = PortfolioSkill::findOrFail($id);
            $this->skillCategory  = $skill->category;
            $this->skillItems     = implode(', ', $skill->skills ?? []);
            $this->skillSortOrder = $skill->sort_order;
            $this->skillIsActive  = $skill->is_active;
        } else {
            $this->skillCategory  = '';
            $this->skillItems     = '';
            $this->skillSortOrder = PortfolioSkill::count() + 1;
            $this->skillIsActive  = true;
        }

        $this->showSkillModal = true;
    }

    public function saveSkill(): void
    {
        $this->validate([
            'skillCategory' => 'required|string|max:100',
            'skillItems'    => 'required|string',
        ]);

        $skills = array_values(array_filter(array_map('trim', explode(',', $this->skillItems))));

        $data = [
            'category'   => $this->skillCategory,
            'skills'     => $skills,
            'sort_order' => $this->skillSortOrder,
            'is_active'  => $this->skillIsActive,
        ];

        if ($this->editingSkillId) {
            PortfolioSkill::findOrFail($this->editingSkillId)->update($data);
            session()->flash('success', 'Skill category updated!');
        } else {
            PortfolioSkill::create($data);
            session()->flash('success', 'Skill category created!');
        }

        $this->showSkillModal = false;
    }

    public function deleteSkill(int $id): void
    {
        PortfolioSkill::findOrFail($id)->delete();
        session()->flash('success', 'Skill category deleted.');
    }

    // =========================================================================
    // Inbox Messages
    // =========================================================================
    public function viewMessage(int $id): void
    {
        $this->viewingMessage = ContactMessage::findOrFail($id);
        if (!$this->viewingMessage->is_read) {
            $this->viewingMessage->update(['is_read' => true]);
        }
    }

    public function closeMessageModal(): void
    {
        $this->viewingMessage = null;
    }

    public function deleteMessage(int $id): void
    {
        ContactMessage::findOrFail($id)->delete();
        if ($this->viewingMessage && $this->viewingMessage->id === $id) {
            $this->viewingMessage = null;
        }
        session()->flash('success', 'Message deleted.');
    }

    public function render()
    {
        return view('livewire.admin.portfolio.portfolio-manager', [
            'projectsList'    => PortfolioProject::ordered()->get(),
            'experiencesList' => PortfolioExperience::ordered()->get(),
            'skillsList'      => PortfolioSkill::ordered()->get(),
            'messagesList'    => ContactMessage::latest()->paginate(10),
            'unreadCount'     => ContactMessage::where('is_read', false)->count(),
        ])->layout('layouts.admin', ['title' => 'Portfolio CMS']);
    }
}
