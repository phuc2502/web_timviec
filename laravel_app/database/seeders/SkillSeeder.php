<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'PHP', 'Laravel', 'Symfony', 'JavaScript', 'TypeScript',
            'Vue.js', 'React', 'Angular', 'Node.js', 'Next.js',
            'Python', 'Django', 'Flask', 'Java', 'Spring Boot',
            'C#', 'ASP.NET Core', 'Go', 'Rust', 'Ruby',
            'Ruby on Rails', 'Swift', 'Kotlin', 'Flutter', 'React Native',
            'HTML5', 'CSS3', 'Sass', 'Tailwind CSS', 'SQL',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Docker',
            'Kubernetes', 'AWS', 'Azure', 'Google Cloud', 'Git',
        ];

        foreach ($skills as $name) {
            Skill::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'usage_count' => 0,
                ]
            );
        }
    }
}
