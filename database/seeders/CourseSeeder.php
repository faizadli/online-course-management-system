<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\ChapterContent;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Seeds a realistic catalogue: published courses (with chapters, Video / Blog /
 * PDF lessons, MCQ + essay assessments with rubrics) and one draft course.
 */
class CourseSeeder extends Seeder
{
    private const MP4 = 'https://www.w3schools.com/html/mov_bbb.mp4';

    public function run(): void
    {
        $this->ensureSamplePdf();

        foreach ($this->catalogue() as $index => $data) {
            $instructor = User::query()->where('email', $data['instructor'])->first();
            $category = Category::query()->where('slug', Str::slug($data['category']))->first();

            $course = Course::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'category_id' => $category?->id,
                    'instructor_id' => $instructor?->id,
                    'status' => $data['status'] ?? 'published',
                    'price' => $data['price'],
                    'level' => $data['level'],
                    'language' => 'English',
                    'thumbnail' => 'https://images.unsplash.com/'.$data['image'].'?w=900&h=560&fit=crop',
                    'short_description' => $data['short'],
                    'description' => $data['description'],
                    'enrolments_count' => 0,
                ]
            );

            // Older courses first so "New" badges only appear on the latest ones.
            $age = now()->subDays(120 - ($index * 9));
            $course->forceFill(['created_at' => $age, 'updated_at' => $age])->saveQuietly();

            $this->buildCurriculum($course, $data);
        }
    }

    private function buildCurriculum(Course $course, array $data): void
    {
        $chapters = [
            ['Getting Started', 'Welcome and foundations'],
            ['Core Concepts', 'The ideas you will use every day'],
            ['Practice & Next Steps', 'Apply what you learned'],
        ];

        foreach ($chapters as $i => [$title, $subtitle]) {
            $chapter = Chapter::query()->updateOrCreate(
                ['course_id' => $course->id, 'sort_order' => $i + 1],
                ['title' => $title]
            );

            $order = 1;
            $video = $data['videos'][$i] ?? self::MP4;

            ChapterContent::query()->updateOrCreate(
                ['chapter_id' => $chapter->id, 'sort_order' => $order++],
                [
                    'title' => ['Welcome to '.$course->title, 'Core techniques explained', 'Putting it into practice'][$i],
                    'type' => 'video',
                    'url' => $video,
                    'body' => $subtitle.'. Follow along with the instructor and take notes.',
                    'duration_minutes' => [8, 14, 12][$i],
                    'is_free_preview' => $i === 0,
                ]
            );

            ChapterContent::query()->updateOrCreate(
                ['chapter_id' => $chapter->id, 'sort_order' => $order++],
                [
                    'title' => ['Course overview & key terms', 'Reading: '.$data['points'][0], 'Checklist: '.$data['points'][2]][$i],
                    'type' => 'text',
                    'body' => $this->lessonHtml($course->title, $i, $data['points']),
                    'duration_minutes' => [6, 10, 7][$i],
                    'is_free_preview' => false,
                ]
            );

            if ($i === 1) {
                ChapterContent::query()->updateOrCreate(
                    ['chapter_id' => $chapter->id, 'sort_order' => $order++],
                    [
                        'title' => 'Downloadable handout (PDF)',
                        'type' => 'pdf',
                        'file_path' => 'documents/skillforge-sample-handout.pdf',
                        'duration_minutes' => 5,
                    ]
                );
            }
        }

        // Assessments (published for published courses).
        $published = $course->status === 'published' ? 'published' : 'draft';
        $first = $course->chapters()->orderBy('sort_order')->first();
        $last = Chapter::query()->where('course_id', $course->id)->orderByDesc('sort_order')->first();

        $mcq = Assessment::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Chapter 1 Quiz'],
            [
                'chapter_id' => $first->id,
                'type' => 'mcq',
                'description' => 'Check your understanding of the fundamentals. Choose the best answer for each question.',
                'pass_score' => 70,
                'time_limit_minutes' => 15,
                'status' => $published,
            ]
        );

        foreach ($data['mcq'] as $q => [$question, $options, $correct, $explanation]) {
            AssessmentQuestion::query()->updateOrCreate(
                ['assessment_id' => $mcq->id, 'sort_order' => $q + 1],
                [
                    'question' => $question,
                    'options' => collect($options)->map(fn ($text, $k) => ['text' => $text, 'is_correct' => $k === $correct])->all(),
                    'explanation' => $explanation,
                    'points' => 1,
                ]
            );
        }

        $essay = Assessment::query()->updateOrCreate(
            ['course_id' => $course->id, 'title' => 'Final Reflection (Essay)'],
            [
                'chapter_id' => $last->id,
                'type' => 'essay',
                'description' => 'Write a clear, well-structured answer in your own words. Your response is scored by AI against the rubric.',
                'pass_score' => 60,
                'time_limit_minutes' => 30,
                'status' => $published,
            ]
        );

        [$prompt, $model] = $data['essay'];

        AssessmentQuestion::query()->updateOrCreate(
            ['assessment_id' => $essay->id, 'sort_order' => 1],
            [
                'question' => $prompt,
                'model_answer' => $model,
                'rubric' => [
                    ['criteria' => 'Correctness of Concept', 'description' => 'Accurately explains the key ideas', 'weight' => 40],
                    ['criteria' => 'Use of Examples', 'description' => 'Gives a valid, clearly explained real-world example', 'weight' => 35],
                    ['criteria' => 'Clarity of Explanation', 'description' => 'Well organised, coherent and free of major grammar errors', 'weight' => 25],
                ],
                'points' => 10,
            ]
        );
    }

    private function lessonHtml(string $title, int $chapter, array $points): string
    {
        $items = collect($points)->map(fn ($p) => '<li>'.e($p).'</li>')->implode('');
        $heading = ['Overview', 'Key ideas', 'Practice checklist'][$chapter];

        return '<h2>'.e($heading).' — '.e($title).'</h2>'
            .'<p>This lesson summarises what you need to know before moving on. Read it carefully, then complete the activity at the end.</p>'
            .'<h3>What you will cover</h3><ul>'.$items.'</ul>'
            .'<blockquote>Tip: write down one question after every lesson — it makes revision much faster.</blockquote>'
            .'<h3>Quick reference</h3>'
            .'<table><thead><tr><th>Concept</th><th>Why it matters</th></tr></thead><tbody>'
            .'<tr><td>'.e($points[0]).'</td><td>Builds the foundation for everything else in the course.</td></tr>'
            .'<tr><td>'.e($points[1]).'</td><td>Used in almost every real project you will complete.</td></tr>'
            .'<tr><td>'.e($points[2]).'</td><td>Helps you review and improve your own work.</td></tr>'
            .'</tbody></table>'
            .'<p>When you are ready, mark this lesson as complete and continue to the next one.</p>';
    }

    /** Creates a tiny but valid PDF so the PDF viewer can be demonstrated offline. */
    private function ensureSamplePdf(): void
    {
        $path = storage_path('app/public/documents/skillforge-sample-handout.pdf');

        if (File::exists($path)) {
            return;
        }

        File::ensureDirectoryExists(dirname($path));

        $stream = "BT /F1 22 Tf 72 740 Td (SkillForge Course Handout) Tj ET\n"
            ."BT /F1 12 Tf 72 700 Td (This sample PDF is displayed inside the lesson viewer.) Tj ET\n"
            ."BT /F1 12 Tf 72 680 Td (Learners can read it inline or download a copy.) Tj ET\n"
            ."BT /F1 12 Tf 72 640 Td (1. Review the lesson notes after every video.) Tj ET\n"
            ."BT /F1 12 Tf 72 620 Td (2. Complete the quiz to check your understanding.) Tj ET\n"
            ."BT /F1 12 Tf 72 600 Td (3. Submit the essay to receive AI feedback.) Tj ET";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";

        File::put($path, $pdf);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function catalogue(): array
    {
        return [
            [
                'title' => 'Photography for Beginners', 'category' => 'Photography', 'instructor' => 'alex@skillforge.com', 'price' => 39, 'level' => 'Beginner',
                'image' => 'photo-1452587925148-ce544e77e70d',
                'short' => 'Learn exposure, composition and light to take confident photos with any camera.',
                'description' => "Photography for Beginners takes you from auto mode to creative control. You will learn how aperture, shutter speed and ISO work together, how to compose a frame, and how to use natural light.\n\nBy the end you will have a small portfolio of photos you are proud of.",
                'points' => ['The exposure triangle', 'Rule of thirds and framing', 'Working with natural light'],
                'videos' => ['https://www.youtube.com/watch?v=LxO-6rlihSg', self::MP4, self::MP4],
                'mcq' => [
                    ['Which setting controls how much light enters the lens?', ['Aperture', 'White balance', 'Burst mode', 'Zoom ring'], 0, 'Aperture is the opening in the lens that controls the amount of light.'],
                    ['What does a faster shutter speed help you do?', ['Blur the background', 'Freeze motion', 'Increase zoom', 'Lower the ISO to zero'], 1, 'A fast shutter speed freezes moving subjects.'],
                ],
                'essay' => ['Explain the exposure triangle and describe how you would adjust it to photograph a moving subject in low light.', 'The exposure triangle is aperture, shutter speed and ISO. For a moving subject in low light, use a wide aperture and a fast enough shutter speed to freeze motion, then raise ISO to compensate while accepting more noise.'],
            ],
            [
                'title' => 'Public Speaking Confidence', 'category' => 'Personal Development', 'instructor' => 'alex@skillforge.com', 'price' => 29, 'level' => 'Beginner',
                'image' => 'photo-1475721027785-f74eccf877e2',
                'short' => 'Overcome nerves and deliver clear, persuasive presentations.',
                'description' => 'Learn practical techniques to manage anxiety, structure a talk, and connect with any audience.',
                'points' => ['Managing speaking anxiety', 'Structuring a clear message', 'Body language and voice'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['Which opening is most effective for engaging an audience?', ['Reading your slides aloud', 'A relevant story or question', 'Apologising for being nervous', 'Listing your qualifications'], 1, 'Stories and questions create immediate engagement.'],
                    ['What is a good way to structure a short talk?', ['Random order of ideas', 'Opening, key points, conclusion', 'Only a conclusion', 'A long list of facts'], 1, 'A clear opening, body and conclusion keeps the audience oriented.'],
                ],
                'essay' => ['Describe three techniques you would use to calm nerves before a presentation and explain why each works.', 'Techniques include controlled breathing, rehearsal, and reframing nervousness as excitement. Breathing slows the heart rate, rehearsal builds familiarity, and reframing reduces threat response.'],
            ],
            [
                'title' => 'Financial Literacy 101', 'category' => 'Business & Marketing', 'instructor' => 'emily@skillforge.com', 'price' => 34, 'level' => 'Beginner',
                'image' => 'photo-1554224155-6726b3ff858f',
                'short' => 'Budgeting, saving and investing basics for everyday life.',
                'description' => 'Understand how money works: build a budget, manage debt, and start investing with confidence.',
                'points' => ['Building a monthly budget', 'Saving and emergency funds', 'Introduction to investing'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['What is an emergency fund for?', ['Holiday spending', 'Unexpected expenses', 'Buying stocks', 'Paying subscriptions'], 1, 'An emergency fund covers unexpected costs without debt.'],
                    ['Which rule suggests splitting income 50/30/20?', ['Budgeting rule', 'Compound rule', 'Tax rule', 'Credit rule'], 0, 'The 50/30/20 rule splits income into needs, wants and savings.'],
                ],
                'essay' => ['Explain compound interest and why starting to save early matters. Include a simple example.', 'Compound interest means earning interest on both the principal and previously earned interest. Starting early gives money more time to grow, e.g. 100 at 5% grows to about 163 in 10 years.'],
            ],
            [
                'title' => 'Mindfulness & Productivity', 'category' => 'Personal Development', 'instructor' => 'alex@skillforge.com', 'price' => 35, 'level' => 'Beginner',
                'image' => 'photo-1503676260728-1c00da094a0b',
                'short' => 'Build focus, reduce stress and get more done with simple daily habits.',
                'description' => 'Combine mindfulness practice with proven productivity systems to work calmly and effectively.',
                'points' => ['Daily mindfulness practice', 'Prioritising with the Eisenhower matrix', 'Deep work and focus blocks'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['What does the Eisenhower matrix help you do?', ['Track expenses', 'Prioritise tasks by urgency and importance', 'Schedule meetings', 'Write emails'], 1, 'It sorts tasks into urgent/important quadrants.'],
                    ['A focus block is best described as…', ['Checking messages often', 'Uninterrupted time on one task', 'A coffee break', 'A team meeting'], 1, 'Focus blocks protect deep work from interruptions.'],
                ],
                'essay' => ['Describe how mindfulness can improve productivity and give one practical daily habit you would adopt.', 'Mindfulness trains attention and reduces stress, making it easier to stay on task. A habit such as a five-minute breathing exercise before work helps start the day with focus.'],
            ],
            [
                'title' => 'Graphic Design with Figma', 'category' => 'Design & Creative', 'instructor' => 'instructor@skillforge.com', 'price' => 54, 'level' => 'Intermediate',
                'image' => 'photo-1626785774573-4b799315345d',
                'short' => 'Create professional graphics, components and design systems in Figma.',
                'description' => 'Master Figma frames, auto layout, components and variants to design faster and more consistently.',
                'points' => ['Frames and auto layout', 'Components and variants', 'Colour and typography styles'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['Which Figma feature makes elements resize automatically with their content?', ['Auto layout', 'Plugins', 'Prototype', 'Export'], 0, 'Auto layout adapts spacing and size to content.'],
                    ['What is a component used for?', ['Reusable design elements', 'Exporting PDFs', 'Sharing links', 'Version history'], 0, 'Components let you reuse and update elements consistently.'],
                ],
                'essay' => ['Explain why design systems and components improve consistency, with one real example.', 'Design systems centralise reusable components and styles so changes propagate everywhere. For example, updating a button component updates every instance across screens.'],
            ],
            [
                'title' => 'SEO & Content Strategy', 'category' => 'Business & Marketing', 'instructor' => 'emily@skillforge.com', 'price' => 64, 'level' => 'Intermediate',
                'image' => 'photo-1432888622747-4eb9a8efeb07',
                'short' => 'Rank higher and attract the right visitors with a repeatable content plan.',
                'description' => 'Learn keyword research, on-page SEO and content planning to grow organic traffic.',
                'points' => ['Keyword research', 'On-page optimisation', 'Content calendars'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['What does SEO stand for?', ['Search Engine Optimisation', 'Site Evaluation Order', 'Social Engagement Output', 'Secure Email Option'], 0, 'SEO means Search Engine Optimisation.'],
                    ['Which is an on-page SEO factor?', ['Title tag', 'Weather', 'Server colour', 'Logo size only'], 0, 'Title tags strongly influence search visibility.'],
                ],
                'essay' => ['Explain the difference between on-page and off-page SEO and give one example of each.', 'On-page SEO covers elements on the site such as titles and content; off-page SEO covers external signals such as backlinks from reputable websites.'],
            ],
            [
                'title' => 'Brand Identity Design', 'category' => 'Design & Creative', 'instructor' => 'instructor@skillforge.com', 'price' => 44, 'level' => 'Intermediate', 'status' => 'draft',
                'image' => 'photo-1558655146-9f40138edfeb',
                'short' => 'Build a memorable brand from logo to voice. (Draft)',
                'description' => 'A work-in-progress course on brand strategy and identity design.',
                'points' => ['Brand strategy', 'Logo design principles', 'Brand guidelines'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['A brand identity includes…', ['Logo, colours and voice', 'Only a logo', 'Only prices', 'Only a website'], 0, 'Identity covers visual and verbal elements.'],
                    ['Brand guidelines exist to…', ['Keep branding consistent', 'Increase taxes', 'Replace the logo', 'Hide the brand'], 0, 'Guidelines keep usage consistent.'],
                ],
                'essay' => ['What makes a brand memorable? Discuss with an example.', 'Memorable brands are distinctive, consistent and meaningful, such as brands with a clear logo, colour palette and tone of voice.'],
            ],
            [
                'title' => 'Digital Marketing Mastery', 'category' => 'Business & Marketing', 'instructor' => 'emily@skillforge.com', 'price' => 59, 'level' => 'Intermediate',
                'image' => 'photo-1460925895917-afdab827c52f',
                'short' => 'Plan, launch and measure campaigns across search, social and email.',
                'description' => "Digital Marketing Mastery gives you a complete toolkit: audience research, channel strategy, content creation, paid ads and analytics.\n\nYou will build a full campaign plan you can use for a real business.",
                'points' => ['Defining your target audience', 'Choosing channels and budgets', 'Measuring with KPIs'],
                'videos' => ['https://www.youtube.com/watch?v=bixR-KIJKYM', self::MP4, self::MP4],
                'mcq' => [
                    ['What does CTR stand for?', ['Click-through rate', 'Customer total revenue', 'Content tracking report', 'Campaign time range'], 0, 'CTR = clicks divided by impressions.'],
                    ['Which metric best shows the cost of winning a customer?', ['CPC', 'CAC', 'CPM', 'CTR'], 1, 'Customer acquisition cost (CAC) measures total cost per new customer.'],
                    ['A buyer persona is…', ['A fictional profile of your ideal customer', 'A legal contract', 'An ad format', 'A type of keyword'], 0, 'Personas guide messaging and targeting.'],
                ],
                'essay' => ['Describe how you would plan a digital marketing campaign for a new local coffee shop, including audience, channels and one KPI.', 'Identify the target audience (students and office workers nearby), use Instagram, Google Maps and local email offers, and track a KPI such as foot traffic from coupon redemptions.'],
            ],
            [
                'title' => 'UI/UX Design Fundamentals', 'category' => 'Design & Creative', 'instructor' => 'instructor@skillforge.com', 'price' => 49, 'level' => 'Beginner',
                'image' => 'photo-1561070791-2526d30994b5',
                'short' => 'Master user research, wireframing, prototyping and usability testing.',
                'description' => "Master the core principles of UI/UX design — from user research and wireframing to prototyping and usability testing.\n\nBuild a portfolio-ready project by the end of this course.",
                'points' => ['User research and personas', 'Wireframes and prototypes', 'Usability testing'],
                'videos' => ['https://www.youtube.com/watch?v=c9Wg6Cb_YlU', self::MP4, self::MP4],
                'mcq' => [
                    ['What is the main purpose of a wireframe?', ['Define layout and structure', 'Choose final colours', 'Write the backend', 'Host the website'], 0, 'Wireframes focus on structure before visual design.'],
                    ['Which method gathers feedback from real users on a prototype?', ['Usability testing', 'Code review', 'Load testing', 'A/B billing'], 0, 'Usability testing reveals real user problems.'],
                    ['A persona represents…', ['A typical user segment', 'A database table', 'A colour palette', 'A server'], 0, 'Personas summarise user goals and behaviours.'],
                ],
                'essay' => ['Explain the difference between UI and UX design and describe how user research improves a product.', 'UX is the overall experience and flow; UI is the visual interface. User research uncovers needs and pain points so design decisions are evidence-based rather than assumptions.'],
            ],
            [
                'title' => 'Full Stack Web Development', 'category' => 'Tech & Programming', 'instructor' => 'john@skillforge.com', 'price' => 79, 'level' => 'Intermediate',
                'image' => 'photo-1498050108023-c5249f4df085',
                'short' => 'Build and deploy modern web applications with HTML, CSS, JavaScript and Laravel.',
                'description' => "Learn to build modern web applications from scratch using HTML, CSS, JavaScript, and Laravel.\n\nDeploy a full-stack project to production by the end of the course.",
                'points' => ['HTML, CSS and responsive layout', 'Routing, controllers and Eloquent', 'REST APIs and deployment'],
                'videos' => ['https://www.youtube.com/watch?v=qz0aGYrrlhU', self::MP4, self::MP4],
                'mcq' => [
                    ['In MVC, which part handles the database?', ['Model', 'View', 'Controller', 'Route'], 0, 'Models represent and query data.'],
                    ['Which HTTP method is normally used to create a resource?', ['GET', 'POST', 'DELETE', 'HEAD'], 1, 'POST creates new resources.'],
                    ['What does CSS primarily control?', ['Presentation and layout', 'Database queries', 'Server routing', 'Encryption'], 0, 'CSS styles how pages look.'],
                ],
                'essay' => ['Explain the difference between a stack and a queue, and provide one real-world example of each.', 'A stack is LIFO (last in, first out), like a pile of plates. A queue is FIFO (first in, first out), like a line at a ticket counter.'],
            ],
            [
                'title' => 'Python Data Science Bootcamp', 'category' => 'Tech & Programming', 'instructor' => 'john@skillforge.com', 'price' => 89, 'level' => 'Intermediate',
                'image' => 'photo-1555949963-aa79d882981c',
                'short' => 'Analyse data and build models with Python, pandas and scikit-learn.',
                'description' => 'A hands-on introduction to data analysis, visualisation and machine learning using Python.',
                'points' => ['Python basics for data work', 'Cleaning data with pandas', 'Training and evaluating models'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['Which library is commonly used for tabular data in Python?', ['pandas', 'flask', 'pygame', 'tkinter'], 0, 'pandas provides DataFrames for tabular data.'],
                    ['What is overfitting?', ['Model fits training data too closely', 'Model is too small', 'Data is missing', 'Training is fast'], 0, 'Overfit models perform poorly on new data.'],
                ],
                'essay' => ['Explain the difference between supervised and unsupervised learning and give one example of each.', 'Supervised learning uses labelled data to predict outcomes (e.g. spam detection). Unsupervised learning finds structure in unlabelled data (e.g. customer clustering).'],
            ],
            [
                'title' => 'Mobile App Development', 'category' => 'Tech & Programming', 'instructor' => 'john@skillforge.com', 'price' => 99, 'level' => 'Advanced',
                'image' => 'photo-1512941937669-90a1b58e7e9c',
                'short' => 'Design, build and ship cross-platform mobile apps.',
                'description' => 'Go from idea to app-store release: UI patterns, state management, APIs and publishing.',
                'points' => ['Mobile UI patterns', 'State management', 'Publishing to app stores'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['What is the benefit of cross-platform development?', ['One codebase for several platforms', 'No testing needed', 'No internet required', 'Free hosting'], 0, 'Shared code reduces cost and time.'],
                    ['Which concept manages data shared across screens?', ['State management', 'Compression', 'Pagination', 'Hashing'], 0, 'State management keeps UI and data in sync.'],
                ],
                'essay' => ['Discuss two key differences between designing for mobile and designing for desktop.', 'Mobile screens are smaller and touch-driven, requiring larger tap targets and simpler navigation; desktop allows denser layouts and hover interactions.'],
            ],
            [
                'title' => 'Study Skills Starter Kit', 'category' => 'Personal Development', 'instructor' => 'alex@skillforge.com', 'price' => 0, 'level' => 'Beginner',
                'image' => 'photo-1456513080510-7bf3a84b82f8',
                'short' => 'A free mini-course on note-taking, memory and exam preparation.',
                'description' => 'Free introduction to effective study habits: active recall, spaced repetition and note-taking methods.',
                'points' => ['Active recall', 'Spaced repetition', 'Effective note-taking'],
                'videos' => [self::MP4, self::MP4, self::MP4],
                'mcq' => [
                    ['Which technique involves testing yourself instead of re-reading?', ['Active recall', 'Highlighting', 'Skimming', 'Copying notes'], 0, 'Retrieval practice strengthens memory.'],
                    ['Spaced repetition means…', ['Reviewing at increasing intervals', 'Cramming the night before', 'Studying once', 'Avoiding review'], 0, 'Spacing improves long-term retention.'],
                ],
                'essay' => ['Explain how active recall differs from re-reading notes and why it is more effective.', 'Active recall forces the brain to retrieve information, strengthening memory, while re-reading creates an illusion of familiarity without real retrieval practice.'],
            ],
        ];
    }
}
