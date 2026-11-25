<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ItemRequest;
use App\Models\Notification;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * التقييم المتبادل: بعد انتهاء العملية كل طرف بيقيم التاني مرة وحدة
 */
class ReviewController extends Controller
{
    public function store(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        $user = $request->user();

        abort_unless($itemRequest->isParty($user), 403);

        if ($itemRequest->status !== 'finished') {
            return back()->with(['toast' => 'يمكنك التقييم بعد إتمام العملية', 'toast_type' => 'error']);
        }

        $alreadyReviewed = Review::where('request_id', $itemRequest->id)->where('reviewer_id', $user->id)->exists();
        if ($alreadyReviewed) {
            return back()->with(['toast' => 'قمت بتقييم هذه العملية مسبقاً', 'toast_type' => 'error']);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ], [
            'rating.*' => 'اختر تقييماً من 1 إلى 5 نجوم',
            'comment.*' => 'التعليق 500 حرف كحد أقصى',
        ]);

        $other = $itemRequest->otherParty($user);

        $review = Review::create([
            'request_id' => $itemRequest->id,
            'reviewer_id' => $user->id,
            'reviewed_user_id' => $other->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        // التقييم النهائي للطرف التاني
        Review::refreshUserRate($other->id);

        ActivityLog::record('create', $review, null, ['rating' => $data['rating']]);
        Notification::send($other->id, 'تقييم جديد', 'حصلت على تقييم '.$data['rating'].' نجوم من '.$user->full_name.'.', route('profile.show'));

        return back()->with('toast', 'شكراً لك! تم حفظ تقييمك');
    }
}
