<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Support\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class BangladeshTuitionTaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slugService = app(SlugService::class);

        $country = Country::query()->withTrashed()->firstOrNew([
            'name' => 'Bangladesh',
        ]);

        $country->fill([
            'slug' => $slugService->unique(
                Country::class,
                'bangladesh',
                $country->exists ? $country->getKey() : null,
            ),
            'status' => Country::STATUS_ACTIVE,
        ])->save();

        $this->restoreIfTrashed($country);

        $cityAndAreaData = [
            [
                'name' => 'Dhaka',
                'areas' => ['Dhanmondi', 'Mirpur', 'Uttara', 'Mohammadpur', 'Bashundhara R/A', 'Badda'],
            ],
            [
                'name' => 'Chattogram',
                'areas' => ['GEC Circle', 'Halishahar', 'Agrabad', 'Panchlaish', 'Nasirabad', 'Patenga'],
            ],
            [
                'name' => 'Rajshahi',
                'areas' => ['Boalia', 'Shaheb Bazar', 'Laxmipur', 'Kazla', 'Talaimari'],
            ],
            [
                'name' => 'Khulna',
                'areas' => ['Sonadanga', 'Khalishpur', 'Daulatpur', 'Boyra'],
            ],
            [
                'name' => 'Sylhet',
                'areas' => ['Zindabazar', 'Amberkhana', 'Shahjalal Upashahar', 'Tilagor'],
            ],
        ];

        foreach ($cityAndAreaData as $cityData) {
            $city = City::query()->withTrashed()->firstOrNew([
                'country_id' => $country->id,
                'name' => $cityData['name'],
            ]);

            $city->fill([
                'slug' => $slugService->unique(
                    City::class,
                    $cityData['name'],
                    $city->exists ? $city->getKey() : null,
                    true,
                    ['country_id' => $country->id],
                ),
                'status' => City::STATUS_ACTIVE,
            ])->save();

            $this->restoreIfTrashed($city);

            foreach ($cityData['areas'] as $areaName) {
                $area = Area::query()->withTrashed()->firstOrNew([
                    'city_id' => $city->id,
                    'name' => $areaName,
                ]);

                $area->fill([
                    'slug' => $slugService->unique(
                        Area::class,
                        $areaName,
                        $area->exists ? $area->getKey() : null,
                        true,
                        ['city_id' => $city->id],
                    ),
                    'status' => Area::STATUS_ACTIVE,
                ])->save();

                $this->restoreIfTrashed($area);
            }
        }

        $categoryData = [
            [
                'name' => 'Bangla Medium',
                'description' => 'School-level tutoring for Bangla medium curriculum from primary to SSC.',
                'classes' => [
                    [
                        'name' => 'Class 5',
                        'subjects' => ['Mathematics', 'English', 'Bangla', 'General Science'],
                    ],
                    [
                        'name' => 'Class 8',
                        'subjects' => ['Mathematics', 'English 1st Paper', 'English 2nd Paper', 'ICT', 'Bangladesh and Global Studies'],
                    ],
                    [
                        'name' => 'SSC Science (Class 10)',
                        'subjects' => ['Physics', 'Chemistry', 'Higher Mathematics', 'Biology'],
                    ],
                ],
            ],
            [
                'name' => 'English Medium',
                'description' => 'Tutoring support for Cambridge and Edexcel English medium students.',
                'classes' => [
                    [
                        'name' => 'Grade 5 (Cambridge)',
                        'subjects' => ['Mathematics', 'English', 'Science', 'Global Perspectives'],
                    ],
                    [
                        'name' => 'O Level',
                        'subjects' => ['Mathematics D', 'Physics', 'Chemistry', 'Biology', 'English Language'],
                    ],
                    [
                        'name' => 'A Level',
                        'subjects' => ['Pure Mathematics', 'Physics', 'Chemistry', 'Economics'],
                    ],
                ],
            ],
            [
                'name' => 'English Version',
                'description' => 'English Version NCTB curriculum tutoring support.',
                'classes' => [
                    [
                        'name' => 'Class 9 Science (English Version)',
                        'subjects' => ['Physics', 'Chemistry', 'Higher Mathematics', 'Biology'],
                    ],
                ],
            ],
            [
                'name' => 'Madrasah',
                'description' => 'Dakhil and Alim stream tutoring with religion and general subjects.',
                'classes' => [
                    [
                        'name' => 'Dakhil (Class 10)',
                        'subjects' => ['Quran Majid and Tajweed', 'Arabic 1st Paper', 'English', 'Mathematics', 'General Science'],
                    ],
                ],
            ],
            [
                'name' => 'University Admission',
                'description' => 'Admission preparation for engineering, medical, and public university exams.',
                'classes' => [
                    [
                        'name' => 'Medical Admission',
                        'subjects' => ['Biology', 'Chemistry', 'Physics', 'English'],
                    ],
                    [
                        'name' => 'Engineering Admission',
                        'subjects' => ['Physics', 'Chemistry', 'Higher Mathematics', 'ICT'],
                    ],
                    [
                        'name' => 'University A Unit',
                        'subjects' => ['Bangla', 'English', 'General Knowledge'],
                    ],
                ],
            ],
            [
                'name' => 'University Undergraduate',
                'description' => 'Undergraduate tutoring for university-level academic support.',
                'classes' => [
                    [
                        'name' => 'CSE 1st Year',
                        'subjects' => ['Structured Programming', 'Calculus', 'Physics', 'Discrete Mathematics'],
                    ],
                    [
                        'name' => 'BBA 1st Year',
                        'subjects' => ['Principles of Management', 'Microeconomics', 'Business Mathematics', 'English Composition'],
                    ],
                ],
            ],
        ];

        foreach ($categoryData as $categoryIndex => $categoryRow) {
            $category = Category::query()->withTrashed()->firstOrNew([
                'name' => $categoryRow['name'],
            ]);

            $category->fill([
                'slug' => $slugService->unique(
                    Category::class,
                    $categoryRow['name'],
                    $category->exists ? $category->getKey() : null,
                ),
                'description' => $categoryRow['description'],
                'status' => Category::STATUS_ACTIVE,
                'sort_order' => $categoryIndex + 1,
            ])->save();

            $this->restoreIfTrashed($category);

            foreach ($categoryRow['classes'] as $classIndex => $classRow) {
                $schoolClass = SchoolClass::query()->withTrashed()->firstOrNew([
                    'category_id' => $category->id,
                    'name' => $classRow['name'],
                ]);

                $schoolClass->fill([
                    'slug' => $slugService->unique(
                        SchoolClass::class,
                        $classRow['name'],
                        $schoolClass->exists ? $schoolClass->getKey() : null,
                        true,
                        ['category_id' => $category->id],
                    ),
                    'status' => SchoolClass::STATUS_ACTIVE,
                    'sort_order' => $classIndex + 1,
                ])->save();

                $this->restoreIfTrashed($schoolClass);

                foreach ($classRow['subjects'] as $subjectIndex => $subjectName) {
                    $subject = Subject::query()->withTrashed()->firstOrNew([
                        'class_id' => $schoolClass->id,
                        'name' => $subjectName,
                    ]);

                    $subject->fill([
                        'slug' => $slugService->unique(
                            Subject::class,
                            $subjectName,
                            $subject->exists ? $subject->getKey() : null,
                            true,
                            ['class_id' => $schoolClass->id],
                        ),
                        'status' => Subject::STATUS_ACTIVE,
                        'sort_order' => $subjectIndex + 1,
                    ])->save();

                    $this->restoreIfTrashed($subject);
                }
            }
        }

        $tuitionTypeData = [
            [
                'name' => 'Home Tutoring',
                'description' => 'One-to-one in-person tutoring at the student home.',
            ],
            [
                'name' => 'Online Tutoring',
                'description' => 'Live online tutoring over Zoom or Google Meet.',
            ],
            [
                'name' => 'Group Tutoring',
                'description' => 'Small batch tutoring for two to five students.',
            ],
            [
                'name' => 'Coaching Batch',
                'description' => 'Large-batch coaching for board and admission preparation.',
            ],
            [
                'name' => 'Exam Crash Program',
                'description' => 'Short-term revision classes before exams.',
            ],
            [
                'name' => 'Shadow Teacher Support',
                'description' => 'Dedicated teacher support during school hours or exam periods.',
            ],
        ];

        foreach ($tuitionTypeData as $index => $typeRow) {
            $tuitionType = TuitionType::query()->withTrashed()->firstOrNew([
                'name' => $typeRow['name'],
            ]);

            $tuitionType->fill([
                'slug' => $slugService->unique(
                    TuitionType::class,
                    $typeRow['name'],
                    $tuitionType->exists ? $tuitionType->getKey() : null,
                ),
                'description' => $typeRow['description'],
                'status' => TuitionType::STATUS_ACTIVE,
                'sort_order' => $index + 1,
            ])->save();

            $this->restoreIfTrashed($tuitionType);
        }
    }

    /**
     * Restore a soft-deleted model instance when found in trashed state.
     */
    private function restoreIfTrashed(Model $model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }
}
