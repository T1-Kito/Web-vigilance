<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateFakeReviews extends Command
{
    protected $signature = 'reviews:fake {--count=15 : Số reviews mỗi sản phẩm} {--clear : Xóa reviews cũ trước khi tạo}';
    protected $description = 'Tạo dữ liệu đánh giá ảo cho sản phẩm';

    // Tên người Việt Nam thực tế
    private $vietnameseNames = [
        'Nguyễn Văn An', 'Trần Thị Bình', 'Lê Hoàng Cường', 'Phạm Minh Đức', 'Hoàng Thị Mai',
        'Vũ Đình Hải', 'Đặng Thị Hương', 'Bùi Quốc Khánh', 'Đỗ Thị Lan', 'Ngô Văn Long',
        'Dương Thị Ngọc', 'Lý Văn Phong', 'Hồ Thị Quỳnh', 'Trịnh Văn Sơn', 'Mai Thị Tâm',
        'Đinh Văn Tùng', 'Phan Thị Uyên', 'Võ Minh Vũ', 'Lương Thị Xuân', 'Tô Văn Yên',
        'Nguyễn Thị Ánh', 'Trần Văn Bảo', 'Lê Thị Cẩm', 'Phạm Văn Dũng', 'Hoàng Thị Giang',
        'Vũ Văn Hùng', 'Đặng Thị Kim', 'Bùi Văn Lâm', 'Đỗ Thị Mỹ', 'Ngô Văn Nam',
        'Chu Thị Oanh', 'Lý Văn Phúc', 'Hồ Thị Quyên', 'Trịnh Văn Sang', 'Mai Thị Thảo',
        'Đinh Văn Toàn', 'Phan Thị Vân', 'Võ Minh Quang', 'Lương Thị Yến', 'Tô Văn Hào',
    ];

    // Trả lời mẫu từ shop (giống CellphoneS)
    private $shopReplies = [
        'Cảm ơn bạn đã tin tưởng và ủng hộ Vigilance ạ! 💙',
        'Vigilance xin cảm ơn bạn đã dành thời gian đánh giá sản phẩm. Chúc bạn sử dụng sản phẩm vui vẻ ạ! 🙏',
        'Dạ cảm ơn bạn đã chia sẻ trải nghiệm. Vigilance rất vui vì bạn hài lòng với sản phẩm ạ! ❤️',
        'Cảm ơn bạn đã đánh giá. Mọi thắc mắc bạn vui lòng liên hệ hotline để được hỗ trợ nhanh nhất ạ!',
        'Vigilance xin ghi nhận đánh giá của bạn. Chúng mình sẽ cố gắng phục vụ bạn tốt hơn nữa ạ! 💪',
        'Cảm ơn bạn đã tin tưởng mua hàng tại Vigilance. Hẹn gặp lại bạn ở những đơn hàng tiếp theo ạ! 🎉',
        'Dạ Vigilance cảm ơn bạn nhiều ạ! Chúc bạn có trải nghiệm tuyệt vời với sản phẩm nhé! ✨',
        'Cảm ơn feedback của bạn. Vigilance luôn nỗ lực mang đến sản phẩm chất lượng nhất ạ!',
        'Rất vui vì sản phẩm đáp ứng được nhu cầu của bạn. Cảm ơn bạn đã ủng hộ Vigilance ạ! 🌟',
        'Dạ cảm ơn bạn! Nếu cần hỗ trợ gì thêm, bạn cứ inbox cho Vigilance nhé ạ! 💬',
    ];

    // Nội dung đánh giá mẫu
    private $reviewContents = [
        5 => [
            'Sản phẩm rất tốt, đúng như mô tả. Giao hàng nhanh, đóng gói cẩn thận. 👍',
            'Chất lượng tuyệt vời, hoạt động ổn định. Rất hài lòng với sản phẩm này!',
            'Mình đã dùng được 2 tuần, rất OK. Giá cả hợp lý, chất lượng tốt.',
            'Sản phẩm chính hãng, bảo hành đầy đủ. Shop tư vấn nhiệt tình. ⭐⭐⭐⭐⭐',
            'Đã mua lần 2, lần nào cũng hài lòng. Sẽ ủng hộ shop dài dài!',
            'Hàng đẹp, chất lượng cao. Nhân viên hỗ trợ rất nhiệt tình và chuyên nghiệp.',
            'Sản phẩm vượt mong đợi, hoạt động mượt mà. Đáng đồng tiền bát gạo!',
            'Giao hàng siêu nhanh, sản phẩm nguyên seal. Rất tin tưởng shop!',
        ],
        4 => [
            'Sản phẩm tốt, chỉ tiếc là giao hàng hơi lâu. Nhìn chung vẫn hài lòng.',
            'Chất lượng khá ổn với mức giá này. Sẽ giới thiệu cho bạn bè.',
            'Hàng đúng mô tả, hoạt động tốt. Trừ 1 sao vì đóng gói chưa kỹ lắm.',
            'Sản phẩm OK, nhưng hướng dẫn sử dụng có thể chi tiết hơn.',
            'Mua về dùng tốt, giá cả phải chăng. Sẽ mua thêm sản phẩm khác.',
        ],
        3 => [
            'Sản phẩm tạm được, không quá xuất sắc nhưng cũng không tệ.',
            'Chất lượng trung bình, phù hợp với giá tiền.',
            'Hàng OK, giao hàng hơi lâu. Mong shop cải thiện.',
        ],
    ];

    public function handle()
    {
        $count = (int) $this->option('count');
        
        if ($this->option('clear')) {
            // Xóa replies trước, sau đó xóa reviews cha
            Review::whereNotNull('parent_id')->delete();
            Review::whereNull('parent_id')->delete();
            $this->info('Đã xóa tất cả reviews cũ.');
        }

        // Tạo fake users nếu chưa có đủ
        $existingUsers = User::where('email', 'like', 'fakeuser%@example.com')->get();
        $neededUsers = 40 - $existingUsers->count();
        
        if ($neededUsers > 0) {
            $this->info("Tạo $neededUsers fake users...");
            for ($i = $existingUsers->count() + 1; $i <= 40; $i++) {
                User::create([
                    'name' => $this->vietnameseNames[$i - 1] ?? "Khách hàng $i",
                    'email' => "fakeuser{$i}@example.com",
                    'password' => Hash::make('password123'),
                ]);
            }
        }

        $fakeUsers = User::where('email', 'like', 'fakeuser%@example.com')->get();
        $products = Product::active()->get();

        $this->info("Tạo reviews cho {$products->count()} sản phẩm...");
        $bar = $this->output->createProgressBar($products->count());

        foreach ($products as $product) {
            // Random số lượng reviews (10-20)
            $reviewCount = rand(10, min($count, 20));
            $usedUserIds = [];

            for ($i = 0; $i < $reviewCount; $i++) {
                // Chọn user ngẫu nhiên chưa đánh giá SP này
                $availableUsers = $fakeUsers->whereNotIn('id', $usedUserIds);
                if ($availableUsers->isEmpty()) break;
                
                $user = $availableUsers->random();
                $usedUserIds[] = $user->id;

                // Rating thiên về 4-5 sao (thực tế hơn)
                $rating = $this->getWeightedRating();
                $performanceRating = rand(0, 1) ? rand(3, 5) : null;
                $durabilityRating = rand(0, 1) ? rand(3, 5) : null;

                // Lấy nội dung phù hợp với rating
                $contents = $this->reviewContents[$rating] ?? $this->reviewContents[5];
                $content = rand(0, 1) ? $contents[array_rand($contents)] : null;

                // Random ngày trong 6 tháng gần đây
                $createdAt = now()->subDays(rand(1, 180));

                $review = Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'performance_rating' => $performanceRating,
                    'durability_rating' => $durabilityRating,
                    'content' => $content,
                    'is_purchased' => rand(0, 1),
                    'is_approved' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // 40% chance tạo reply từ shop (dùng user_id = 1 là admin)
                if ($content && rand(1, 100) <= 40) {
                    $replyCreatedAt = $createdAt->copy()->addHours(rand(1, 48));
                    Review::create([
                        'product_id' => $product->id,
                        'user_id' => 1, // Admin/Shop account
                        'parent_id' => $review->id,
                        'rating' => $rating,
                        'content' => $this->shopReplies[array_rand($this->shopReplies)],
                        'is_approved' => true,
                        'created_at' => $replyCreatedAt,
                        'updated_at' => $replyCreatedAt,
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Hoàn thành tạo reviews ảo!');
        $this->info('Tổng reviews: ' . Review::count());
    }

    private function getWeightedRating(): int
    {
        // Phân bố: 60% 5 sao, 25% 4 sao, 10% 3 sao, 5% 2-1 sao
        $rand = rand(1, 100);
        if ($rand <= 60) return 5;
        if ($rand <= 85) return 4;
        if ($rand <= 95) return 3;
        return rand(1, 2);
    }
}
