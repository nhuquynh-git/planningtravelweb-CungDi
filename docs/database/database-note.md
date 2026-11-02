erDiagram
	direction TB
	USER {
		string id PK ""  
		string name  ""  
		string email UK ""  
		string password_hash  ""  
		string avatar_url  ""  
		string subscription_tier  "standard | premium"  
		datetime premium_expires_at  "null nếu standard hoặc is_lifetime = true"  
		boolean is_lifetime  ""  
	}

	SUBSCRIPTION {
		string id PK ""  
		string user_id FK ""  
		string plan_type  "monthly | yearly | lifetime"  
		decimal amount_paid  ""  
		string payment_method  "momo | bank | zalopay"  
		string transaction_ref UK "mã giao dịch từ cổng thanh toán"  
		string status  "pending | success | failed | refunded"  
		datetime started_at  ""  
		datetime expires_at  "null nếu lifetime"  
		datetime created_at  ""  
	}

	PLAN {
		string id PK ""  
		string owner_id FK ""  
		string invite_code UK "mã mời tham gia plan"  
		string title  ""  
		string destination  "điểm đến, dùng để tìm kiếm và lọc"  
		string description  ""  
		string type  "nghỉ dưỡng | sinh thái | khám phá | mạo hiểm | giải trí"  
		string plan_photo_url  ""  
		string visibility  "private | public"  
		decimal estimated_total_cost  "nếu plan để public thì tất cả mọi người đều xem được"  
		decimal actual_total_cost  "chỉ plan member được xem"  
		datetime start_date  ""  
		datetime end_date  ""  
		datetime created_at  ""  
	}

	PLAN_MEMBER {
		string id PK ""  
		string user_id FK ""  
		string plan_id FK ""  
		string role  "truong_nhom | thanh_vien"  
		string status  "active | inactive | pending "  
		datetime joined_at  ""  
	}

	PLAN_LIKE {
		string id PK ""  
		string user_id FK ""  
		string plan_id FK ""  
		datetime created_at  ""  
	}

	PLAN_SAVE {
		string id PK ""  
		string user_id FK ""  
		string plan_id FK ""  
		datetime created_at  ""  
	}

	PLAN_COMMENT {
		string id PK ""  
		string plan_id FK ""  
		string user_id FK ""  
		string parent_comment_id FK "null nếu là bình luận gốc"  
		string content  ""  
		string status  "visible | hidden | deleted"  
		datetime created_at  ""  
		datetime updated_at  ""  
	}

	PLAN_SHARE {
		string id PK ""  
		string plan_id FK ""  
		string user_id FK "null nếu là khách"  
		string channel  "copy_link | facebook | zalo | other"  
		datetime created_at  ""  
	}

	LOCATION_REVIEW {
		string id PK ""  
		string user_id FK "unique"  
		string location_id FK "unique"  
		int rating  "1-5"  
		string comment  ""  
		datetime created_at  ""  
		string photo_url "null nếu không kèm ảnh"
	}

	ROUTE {
		string id PK ""  
		string plan_id FK ""  
		string name  ""  
		int order_index  ""  
		decimal total_distance  ""  
		int total_duration_minutes  ""  
	}

	EXPENSE_SHARE {
		string id PK ""  
		string plan_member_id FK ""  
		string location_item_id FK ""  
		decimal amount_owed  ""  
		decimal amount_paid  ""  
		boolean is_paid  ""  
		datetime paid_at  ""  
	}

	LOCATION_ITEM {
		string id PK ""  
		string route_id FK ""  
		string location_id FK ""  
		string category  "di_chuyen | noi_o | an_uong | ca_phe | vui_choi | mua_sam"  
		int order_index  ""  
		decimal estimated_cost  "tính theo đầu người"  
		decimal actual_cost  ""  
		datetime scheduled_time  ""  
		string note  "ghi chú riêng, ví dụ tên nhà xe, giờ xuất bến"  
		string travel_mode  "motorbike (mặc định) | car | walking"  
	}

	LOCATION {
		string id PK ""  
		string provider  "google | goong | ... nhà cung cấp bản đồ"  
		string provider_place_id UK "unique cùng với provider"  
		datetime coords_cached_at  "lần gần nhất lấy lat lng từ nhà cung cấp"  
		float lat  "cache tạm, làm mới theo điều khoản"  
		float lng  "cache tạm, làm mới theo điều khoản"  
		string name  "cache tạm, tuân theo điều khoản nhà cung cấp"  
		string address  "cache tạm"  
		string description  "cache tạm"  
	}

	AFFILIATE_LINK {
		string id PK ""  
		string location_id FK ""  
		string provider  "booking.com | agoda | klook | traveloka"  
		string link_type  "hotel | ticket | transport"  
		string url  ""  
		boolean active  ""  
	}

	MINI_GAME {
		string id PK ""  
		string name  ""  
		string description  ""  
		string rules  "nội dung luật chơi"  
		int min_players  ""  
		int max_players  ""  
		int min_minutes  ""  
		int max_minutes  ""  
		string cover_url  ""  
	}

	MINI_GAME_TAG {
		string id PK ""  
		string mini_game_id FK ""  
		string tag_type  "equipment | setting (dụng cụ và nơi chơi)"  
		string value  "none|paper|cards|phone|ball hoặc car|table|room|outdoor"  
	}

	NOTIFICATION {
		string id PK ""  
		string recipient_id FK "người nhận"  
		string actor_id FK "người gây ra, null nếu do hệ thống"  
		string type  "plan_like | plan_comment | comment_reply | join_request | join_accepted | join_rejected | member_removed | member_left | cost_updated | saved_plan_unavailable | premium_expiring"  
		string plan_id FK "null nếu không gắn plan"  
		string comment_id FK "null nếu không gắn bình luận"  
		string data  "JSON: tên plan lúc gửi, số tiền, số ngày còn lại..."  
		string group_key  "gộp thông báo cùng loại, ví dụ lượt thích"  
		int count  "số sự kiện được gộp, mặc định 1"  
		boolean is_read  ""  
		datetime created_at  ""  
	}

	USER||--o{SUBSCRIPTION:"đăng ký"
	USER||--o{PLAN:"tạo (owner)"
	USER||--o{PLAN_MEMBER:"tham gia"
	USER||--o{PLAN_LIKE:"like"
	USER||--o{PLAN_SAVE:"lưu"
	USER||--o{PLAN_COMMENT:"viết"
	USER||--o{LOCATION_REVIEW:"viết"
	PLAN||--o{PLAN_MEMBER:"gồm"
	PLAN||--o{ROUTE:"chia thành"
	PLAN||--o{PLAN_LIKE:"được like"
	PLAN||--o{PLAN_SAVE:"được lưu"
	PLAN||--o{PLAN_COMMENT:"có bình luận"
	PLAN_COMMENT|o--o{PLAN_COMMENT:"được trả lời"
	PLAN||--o{EXPENSE_SHARE:"phát sinh"
	PLAN_MEMBER||--o{EXPENSE_SHARE:"gánh chịu"
	USER|o--o{PLAN_SHARE:"chia sẻ"
	PLAN||--o{PLAN_SHARE:"được chia sẻ"
	ROUTE||--o{LOCATION_ITEM:"gồm"
	LOCATION||--o{LOCATION_ITEM:"được chọn trong"
	LOCATION||--o{LOCATION_REVIEW:"được đánh giá"
	LOCATION||--o{AFFILIATE_LINK:"có link đặt dịch vụ"
	LOCATION_ITEM||--o{EXPENSE_SHARE:"chia cho thành viên"
	MINI_GAME||--o{MINI_GAME_TAG:"có nhãn"
	USER||--o{NOTIFICATION:"nhận"
	USER|o--o{NOTIFICATION:"gây ra"
	PLAN|o--o{NOTIFICATION:"liên quan"
	PLAN_COMMENT|o--o{NOTIFICATION:"liên quan"

usecas