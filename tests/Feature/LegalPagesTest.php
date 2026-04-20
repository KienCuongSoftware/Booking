<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_cancellation_refunds_page_is_public(): void
    {
        $this->get(route('legal.cancellation-refunds'))
            ->assertOk()
            ->assertSee(__('Mỗi khách sạn có thể áp dụng chính sách hủy và mức phí riêng'), false);
    }

    public function test_privacy_page_is_public(): void
    {
        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee(__('Quyền riêng tư'), false);
    }

    public function test_terms_page_is_public(): void
    {
        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee(__('Điều khoản sử dụng'), false);
    }
}
