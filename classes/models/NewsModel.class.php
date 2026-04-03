<?php
// ─────────────────────────────────────────────────────────────
//  News Model — Management of FAV updates and articles
//  Following MVC architecture
// ─────────────────────────────────────────────────────────────

class NewsModel
{
    /** @var PDO|null */
    private $pdo;

    public function __construct(PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Returns all news items
     * @return array
     */
    public function getAll(): array
    {
        // For now using static data as per current implementation,
        // but encapsulated in the model for MVC compliance.
        return [
            1 => [
                'id' => 1,
                'title' => 'The New Discovery Map is Here!',
                'category' => 'UPDATE',
                'date' => '2024-03-28',
                'image' => 'https://images.unsplash.com/photo-1526772662000-3f88f10405ff?auto=format&fit=crop&q=80&w=1200',
                'summary' => 'We have officially launched the interactive discovery map. Explore spots in France, Albania, and Vietnam like never before.',
                'content' => "The wait is over! Our team has been working tirelessly to bring you a fully interactive experience.\n\nYou can now filter spots by country, see them on a real-time map, and plan your next journey with ease.\n\nThis update also includes performance improvements and a brand new neo-brutalist interface that makes discovery faster than ever."
            ],
            2 => [
                'id' => 2,
                'title' => 'Spotlight: Hidden Gems of Vietnam',
                'category' => 'DESTINATION',
                'date' => '2024-03-15',
                'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&q=80&w=1200',
                'summary' => 'Discover the most beautiful and less-known locations in Vietnam shared by our community this month.',
                'content' => "Vietnam is a land of breathtaking landscapes and hidden secrets.\n\nThis month, our community has highlighted several \"off the beaten path\" locations in the Sapa region and the hidden beaches of Phu Quoc.\n\nFrom mist-covered mountains to crystal clear waters, these spots represent the true spirit of FAV—finding authentic, uncrowded places."
            ],
            3 => [
                'id' => 3,
                'title' => 'The Future of FAV Travel',
                'category' => 'COMMUNITY',
                'date' => '2024-03-01',
                'image' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&q=80&w=1200',
                'summary' => 'Find out what new features and community events we have planned for the upcoming summer season.',
                'content' => "As we approach the summer season, we are excited to announce our upcoming roadmap.\n\nWe will be introducing \"Community Meetups\" in major cities across Europe and Southeast Asia.\n\nAdditionally, our mobile app is entering public beta next month, allowing you to share spots directly from your travels with offline mapping capabilities."
            ]
        ];
    }

    /**
     * Returns a specific news item by ID
     * @param int $id
     * @return array|null
     */
    public function findById(int $id)
    {
        $all = $this->getAll();
        return $all[$id] ?? null;
    }
}