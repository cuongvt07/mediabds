<?php

namespace App\Http\Controllers\Api;

class LocationApiController extends BaseApiController
{
    public function index()
    {
        return $this->ok([
            [
                'code' => 'hcm',
                'name' => 'TP. Hồ Chí Minh',
                'slug' => 'tphcm',
                'districts' => [
                    ['code' => 'q1', 'name' => 'Quận 1', 'slug' => 'quan-1', 'cityCode' => 'hcm'],
                    ['code' => 'q3', 'name' => 'Quận 3', 'slug' => 'quan-3', 'cityCode' => 'hcm'],
                    ['code' => 'q7', 'name' => 'Quận 7', 'slug' => 'quan-7', 'cityCode' => 'hcm'],
                    ['code' => 'qbt', 'name' => 'Quận Bình Thạnh', 'slug' => 'binh-thanh', 'cityCode' => 'hcm'],
                    ['code' => 'qgv', 'name' => 'Quận Gò Vấp', 'slug' => 'go-vap', 'cityCode' => 'hcm'],
                    ['code' => 'qtd', 'name' => 'TP. Thủ Đức', 'slug' => 'thu-duc', 'cityCode' => 'hcm'],
                ],
            ],
            [
                'code' => 'hn',
                'name' => 'Hà Nội',
                'slug' => 'ha-noi',
                'districts' => [
                    ['code' => 'hk', 'name' => 'Quận Hoàn Kiếm', 'slug' => 'hoan-kiem', 'cityCode' => 'hn'],
                    ['code' => 'bd', 'name' => 'Quận Ba Đình', 'slug' => 'ba-dinh', 'cityCode' => 'hn'],
                    ['code' => 'cg', 'name' => 'Quận Cầu Giấy', 'slug' => 'cau-giay', 'cityCode' => 'hn'],
                    ['code' => 'th', 'name' => 'Quận Tây Hồ', 'slug' => 'tay-ho', 'cityCode' => 'hn'],
                    ['code' => 'hd', 'name' => 'Quận Hà Đông', 'slug' => 'ha-dong', 'cityCode' => 'hn'],
                ],
            ],
            ['code' => 'dnang', 'name' => 'Đà Nẵng', 'slug' => 'da-nang', 'districts' => []],
            ['code' => 'hp', 'name' => 'Hải Phòng', 'slug' => 'hai-phong', 'districts' => []],
            ['code' => 'ct', 'name' => 'Cần Thơ', 'slug' => 'can-tho', 'districts' => []],
            ['code' => 'bd', 'name' => 'Bình Dương', 'slug' => 'binh-duong', 'districts' => []],
            ['code' => 'dn', 'name' => 'Đồng Nai', 'slug' => 'dong-nai', 'districts' => []],
            ['code' => 'brvt', 'name' => 'Bà Rịa - Vũng Tàu', 'slug' => 'ba-ria-vung-tau', 'districts' => []],
        ]);
    }
}
