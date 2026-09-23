<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortfolioProject;
use App\Models\PortfolioExperience;
use App\Models\PortfolioSkill;
use Illuminate\Support\Facades\DB;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings for Portfolio Profile
        $settings = [
            ['key' => 'portfolio_name', 'value' => 'Nasir Ali', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Full Name'],
            ['key' => 'portfolio_title', 'value' => 'Network Engineer & Software Developer', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Professional Title'],
            ['key' => 'portfolio_intro', 'value' => 'I am a full-time Network Engineer at Zong CMPak and a part-time Software Developer with a deep analytical mindset. I specialize in examining large-scale and hyperscale enterprise systems, identifying technical weaknesses, troubleshooting complex issues, and developing practical solutions. I am passionate about innovative ideas, emerging technologies, network automation, and building systems that solve real-world problems. I am flexible, adaptable, and willing to travel whenever professional opportunities require it.', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Professional Introduction'],
            ['key' => 'portfolio_about', 'value' => 'Bridging the critical gap between deep telecommunication infrastructure and modern software engineering. With extensive hands-on enterprise NOC exposure at Zong CMPak and tier-2 TAC experience at Cybernet, I deliver rapid root-cause analysis, streamline packet and routing troubleshooting, and develop resilient automated tooling that prevents operational downtime.', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Detailed About Biography'],
            ['key' => 'portfolio_avatar', 'value' => '', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Profile photograph path'],
            ['key' => 'portfolio_cv_file', 'value' => '', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Uploaded CV/Resume file path'],
            ['key' => 'portfolio_email', 'value' => 'nasirali@capitalcart.pk', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Primary Contact Email'],
            ['key' => 'portfolio_phone', 'value' => '03002922584', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Contact Phone Number'],
            ['key' => 'portfolio_whatsapp', 'value' => '03002922584', 'group' => 'portfolio', 'type' => 'string', 'description' => 'WhatsApp Number'],
            ['key' => 'portfolio_linkedin', 'value' => 'https://linkedin.com/in/itsnasiralii', 'group' => 'portfolio', 'type' => 'string', 'description' => 'LinkedIn Profile URL'],
            ['key' => 'portfolio_github', 'value' => 'https://github.com/itsnasiralii', 'group' => 'portfolio', 'type' => 'string', 'description' => 'GitHub Profile URL'],
            ['key' => 'portfolio_instagram', 'value' => 'https://instagram.com/itsnasiralii', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Instagram Profile URL'],
            ['key' => 'portfolio_availability', 'value' => 'Open to Enterprise Consulting, Network Automation & Full-Stack Projects', 'group' => 'portfolio', 'type' => 'string', 'description' => 'Availability Status'],
            ['key' => 'portfolio_visibility', 'value' => '1', 'group' => 'portfolio', 'type' => 'boolean', 'description' => 'Enable or disable public portfolio view'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        // 2. Experiences
        PortfolioExperience::truncate();
        $experiences = [
            [
                'role' => 'Corporate NOC Engineer',
                'company' => 'Zong CMPak',
                'period' => 'January 2026 – Present',
                'description' => 'Enterprise Network Operations Center monitoring, multi-tier escalation, and real-time incident resolution for corporate and enterprise customers nationwide.',
                'responsibilities' => [
                    'Monitor and support mission-critical enterprise network and corporate telecommunication services.',
                    'Troubleshoot complex connectivity, routing, and high-impact enterprise service incidents.',
                    'Coordinate across Core, RAN, Transmission, and external telecom vendor engineering teams.',
                    'Support VoIP, SIP trunks, PRI links, vPBX instances, enterprise IP transit, and cloud connect services.',
                    'Manage comprehensive incident documentation, Root Cause Analysis (RCA), escalation matrices, and SLA compliance monitoring.',
                    'Develop customized Python-based network-diagnostic scripts and operational automation utilities.'
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'role' => 'Network Engineer (TAC)',
                'company' => 'Cybernet',
                'period' => 'July 2025 – December 2025',
                'description' => 'Tier-2 Technical Assistance Center engineering for enterprise backbone connectivity, GPON, metro ethernet, and dedicated lease circuits.',
                'responsibilities' => [
                    'Provided Tier-2 technical support for nationwide enterprise client networks and core infrastructure.',
                    'Troubleshot enterprise-network connectivity issues, layer-2/layer-3 switching, and BGP/OSPF peering anomalies.',
                    'Supported backbone routing stability, link failovers, and latency optimization for critical customer services.',
                    'Coordinated complex escalations with field operations and mentored junior technical support engineers.'
                ],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'role' => 'Part-Time Software Developer',
                'company' => 'Independent / Solutions Engineering',
                'period' => 'Ongoing',
                'description' => 'Transforming complex network and operational bottlenecks into resilient software architectures, diagnostic tools, and web applications.',
                'responsibilities' => [
                    'Build high-performance web applications, operational utilities, telemetry dashboards, and API services.',
                    'Work extensively with Python, PHP, Laravel, JavaScript, HTML, CSS, MySQL, PostgreSQL, and RESTful APIs.',
                    'Engineer end-to-end e-commerce solutions, payment integrations, and automated messaging workflows.',
                    'Convert daily NOC operational problems into practical, automated, and scalable software solutions.'
                ],
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($experiences as $exp) {
            PortfolioExperience::create($exp);
        }

        // 3. Skills
        PortfolioSkill::truncate();
        $skills = [
            [
                'category' => 'Networking',
                'skills' => ['TCP/IP', 'BGP', 'OSPF', 'VLANs', 'DNS', 'DHCP', 'IPv6'],
                'icon' => 'network',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'category' => 'Telecom',
                'skills' => ['5G', 'LTE', 'RAN', 'SIP', 'PRI', 'VoIP', 'vPBX'],
                'icon' => 'radio',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'category' => 'Network Tools',
                'skills' => ['Huawei NE40', 'Wireshark', 'OpenText NMS', 'SolarWinds', 'Zabbix', 'EVE-NG'],
                'icon' => 'tools',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'Development',
                'skills' => ['Python', 'PHP', 'Laravel', 'JavaScript', 'HTML', 'CSS', 'MySQL'],
                'icon' => 'code',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'category' => 'Automation',
                'skills' => ['Netmiko', 'Scapy', 'PowerShell', 'Shell Scripting', 'REST APIs'],
                'icon' => 'cpu',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'category' => 'Operations',
                'skills' => ['Incident Management', 'RCA', 'SLA Management', 'Vendor Coordination'],
                'icon' => 'activity',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'category' => 'Security',
                'skills' => ['CCNA', 'CompTIA Security+', 'CEH Knowledge', 'Firewall Policies'],
                'icon' => 'shield',
                'sort_order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($skills as $skill) {
            PortfolioSkill::create($skill);
        }

        // 4. Projects
        PortfolioProject::truncate();
        $projects = [
            [
                'title' => 'CapitalCart.pk — Smart E-Commerce Platform',
                'slug' => 'capitalcart-pk',
                'category' => 'E-commerce development',
                'description' => 'A full-stack Pakistani e-commerce platform built with Laravel 11, Livewire 3, and Bootstrap 5. Features frictionless guest checkout, real-time Pakistani mobile validation, direct WhatsApp order automation with stock reservation, cryptographic order IDs, and complete administrative controls.',
                'tech_stack' => ['Laravel 11', 'Livewire 3', 'PHP 8.3', 'MySQL', 'WhatsApp API', 'Docker'],
                'image' => null,
                'demo_url' => 'http://127.0.0.1:8000',
                'github_url' => 'https://github.com/itsnasiralii/capitalcart',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'AutoNet BGP & IP Route Telemetry Suite',
                'slug' => 'autonet-bgp-telemetry',
                'category' => 'Network automation',
                'description' => 'Automated network diagnostic script using Python and Netmiko to continuously poll Huawei NE40 and Cisco edge routers. Detects BGP route flapping, interface CRC errors, and packet drop anomalies, triggering automated NOC alerting before service disruption.',
                'tech_stack' => ['Python 3', 'Netmiko', 'SSH', 'Paramiko', 'Regex', 'Linux'],
                'image' => null,
                'demo_url' => null,
                'github_url' => 'https://github.com/itsnasiralii/autonet-telemetry',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'CNOC Enterprise Outage & SLA Tracker',
                'slug' => 'cnoc-enterprise-tracker',
                'category' => 'CNOC operational tools',
                'description' => 'Real-time operational dashboard for tracking corporate enterprise fiber and microwave outages. Automatically calculates MTTR, monitors SLA countdowns, and generates standardized RCA templates for enterprise account managers.',
                'tech_stack' => ['Python', 'Flask', 'MySQL', 'Bootstrap 5', 'Chart.js'],
                'image' => null,
                'demo_url' => null,
                'github_url' => 'https://github.com/itsnasiralii/cnoc-tracker',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'SIP & VoIP Packet Stream Diagnostic Utility',
                'slug' => 'sip-voip-stream-analyzer',
                'category' => 'Network troubleshooting',
                'description' => 'Diagnostic utility leveraging Wireshark tshark and Scapy to dissect SIP signaling handshakes (INVITE, 180 Ringing, 200 OK, BYE) and RTP stream jitter for vPBX and PRI customers, identifying one-way audio and call drop glitches in seconds.',
                'tech_stack' => ['Scapy', 'Wireshark / TShark', 'Python', 'VoIP / SIP', 'RTP Analysis'],
                'image' => null,
                'demo_url' => null,
                'github_url' => 'https://github.com/itsnasiralii/voip-packet-analyzer',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Network Device Diagnostic Explorer (Gradio UI)',
                'slug' => 'network-device-diagnostic-gradio',
                'category' => 'Python and Gradio applications',
                'description' => 'Interactive web-based interface built with Gradio allowing support technicians to safely run predefined operational queries (ping sweep, traceroute MTR, BGP summary, optics power levels) against enterprise edge nodes without direct CLI credentials.',
                'tech_stack' => ['Python', 'Gradio', 'Netmiko', 'FastAPI', 'Subnet Calc'],
                'image' => null,
                'demo_url' => null,
                'github_url' => 'https://github.com/itsnasiralii/gradio-net-explorer',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Automated NOC Shift Handover & SLA Report Engine',
                'slug' => 'noc-shift-handover-engine',
                'category' => 'Reporting and workflow automation',
                'description' => 'Automated workflow engine consolidating incident tickets from OpenText NMS and email feeds to generate formatted PDF/HTML shift handover reports, tracking pending incidents, vendor escalations, and SLA performance.',
                'tech_stack' => ['Python', 'PowerShell', 'HTML Reports', 'SMTP Automation', 'JSON'],
                'image' => null,
                'demo_url' => null,
                'github_url' => 'https://github.com/itsnasiralii/noc-handover-engine',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($projects as $proj) {
            PortfolioProject::create($proj);
        }
    }
}
