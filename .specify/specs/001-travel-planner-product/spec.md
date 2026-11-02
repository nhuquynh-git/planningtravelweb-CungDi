# Đặc tả sản phẩm: Cùng Đi – Travel Planner

**Feature Directory**: `.specify/specs/001-travel-planner-product`

**Feature Branch**: Chưa tạo (không có hook tạo branch)

**Ngày tạo**: 2026-11-02

**Trạng thái**: Draft — các luồng cốt lõi đã được làm rõ; các quyết định sản phẩm còn mở và nội dung chuyển sang Plan được theo dõi tại §12.

**Đầu vào**: Hai tài liệu người dùng cung cấp về đặc tả sản phẩm và đánh giá có kiểm soát ERD/UI/UX.

## 1. Tổng quan sản phẩm

### 1.1. Bối cảnh và vấn đề

Cùng Đi là ứng dụng web hướng đến việc giúp cá nhân và nhóm lập kế hoạch du lịch,
đồng thời quản lý chi tiêu liên quan. Mô tả trong README xác nhận định hướng này,
nhưng chưa mô tả quy trình nghiệp vụ, cách cộng tác, cấu trúc chuyến đi, cách
tính chi phí hay chức năng của từng vai trò.

Sản phẩm cần cung cấp một quy trình dễ hiểu để người dùng tổ chức thông tin
chuyến đi và theo dõi chi phí mà họ ghi nhận. Đặc tả này phân biệt định hướng
đã xác nhận với các luồng chi tiết đang đề xuất; các đề xuất chưa được xem là
quyết định sản phẩm cho đến khi nhóm phê duyệt.

### 1.2. Mục tiêu

- Hỗ trợ mục tiêu sản phẩm đã xác nhận: lập kế hoạch du lịch và quản lý chi
  tiêu cá nhân/nhóm.
- Xây dựng trải nghiệm nhất quán cho Guest, User và Admin theo quyền đã xác
  nhận; không mở rộng quyền chưa được phê duyệt.
- Đáp ứng các yêu cầu đồ án IS207 về phân tích, thiết kế, xây dựng, kiểm thử,
  triển khai và báo cáo.
- Duy trì khả năng truy vết từ yêu cầu môn học đến yêu cầu, luồng người dùng,
  giao diện, mô hình dữ liệu, tiêu chí chấp nhận và bằng chứng báo cáo.
- Không trình bày tính năng chưa xây dựng hoặc chưa kiểm thử như đã hoàn thành.

### 1.3. Quy ước trạng thái

| Nhãn | Ý nghĩa |
|---|---|
| Đã xác nhận | Có trong mô tả dự án, Constitution hoặc yêu cầu môn học được cung cấp. |
| Đã triển khai | Có bằng chứng trong repository; không đồng nghĩa đã kiểm thử đầy đủ. |
| Đã cấu hình | Có cấu hình hoặc nền tảng trong repository; chưa khẳng định luồng sản phẩm hoàn chỉnh. |
| Đề xuất | Luồng hợp lý để cụ thể hóa sản phẩm nhưng cần nhóm xác nhận trước khi lập kế hoạch thực thi. |
| Chưa quyết định | Thiếu thông tin; phải được trả lời trước khi chốt thiết kế bị ảnh hưởng. |
| Ngoài phạm vi hiện tại | Không thuộc phiên bản đầu nếu chưa được phê duyệt riêng. |

### 1.4. Hiện trạng repository đã kiểm tra

Đây là ảnh chụp hiện trạng repository tại ngày tạo đặc tả, không phải yêu cầu
nghiệp vụ hay xác nhận kiểm thử.

| Khu vực | Trạng thái quan sát được | Ý nghĩa đối với đặc tả |
|---|---|---|
| Mô tả dự án | README nêu lập kế hoạch du lịch và quản lý chi tiêu cá nhân/nhóm. | Xác nhận hướng sản phẩm, chưa xác nhận các quy trình chi tiết. |
| Giao diện | Chỉ thấy màn hình kiểm tra trạng thái dịch vụ và kết nối cơ sở dữ liệu. | Chưa có màn hình nghiệp vụ để đánh giá hoặc suy ra cấu trúc layout. |
| Thiết kế UI/UX | Không tìm thấy file Figma, liên kết Figma hoặc bộ màn hình nghiệp vụ trong repository đã kiểm tra. | Chưa thể kiểm kê hoặc đánh giá một thiết kế sản phẩm có sẵn. |
| Giao diện API | Có luồng kiểm tra sức khỏe và helper định dạng phản hồi; chưa thấy hợp đồng API nghiệp vụ. | Chưa tạo danh sách endpoint trong đặc tả này. |
| Xác thực | Nền tảng xác thực và cấu hình nhóm `user`, `admin` hiện diện. | Chỉ xác nhận nền tảng/cấu hình; chưa thấy luồng đăng nhập API và phân quyền nghiệp vụ hoàn chỉnh. |
| Vai trò | Guest, User và Admin là các vai trò được dự án xác nhận; cấu hình hiện có nhóm User/Admin. | Guest là khách chưa xác thực; không suy ra quyền chi tiết từ tên nhóm. |
| Dữ liệu | Script khởi tạo có bảng `test_connection` phục vụ kiểm tra kết nối. Chưa tìm thấy lược đồ nghiệp vụ hoặc migration nghiệp vụ. | Bảng kiểm tra kết nối không phải entity sản phẩm; cơ sở dữ liệu nghiệp vụ chưa được chốt. |
| Kiểm thử | Có kiểm thử khung và kiểm thử liên quan health check. | Không có bằng chứng trong đặc tả này rằng các luồng sản phẩm đã được kiểm thử. |
| Triển khai | Có cấu hình phát triển bằng Docker Compose; AWS là hướng triển khai đã xác nhận. | Dịch vụ AWS, topology và quy trình vận hành vẫn chưa quyết định. |
| Nghiên cứu | Không tìm thấy tài liệu khảo sát đối thủ hoặc nguồn nghiên cứu sản phẩm trong repository đã kiểm tra. | Khảo sát website tương tự là công việc học phần còn thiếu bằng chứng. |

Các công nghệ, ranh giới kiến trúc và quy tắc API đã thống nhất được quản lý trong
[Project Constitution](../../memory/constitution.md); đặc tả này không thay thế
hoặc lặp lại các quyết định đó.

## Clarifications

### Session 2026-11-02

- Q: Với mỗi khoản chi trong Plan nhóm, hệ thống cần tính phần mỗi thành viên chịu theo cách nào? → A: Lưu người thanh toán và phần chịu chi của từng thành viên theo từng khoản, cho phép chia đều hoặc nhập số tiền tùy chỉnh; tính chênh lệch nhưng không ghi nhận hoàn trả.
- Q: Ai được tạo Plan nhóm và thêm thành viên, và chủ Plan cùng các thành viên được thêm, xem, sửa hoặc xóa khoản chi như thế nào? → A: User tạo Plan là chủ Plan và quản lý danh sách thành viên cùng mọi khoản chi; các thành viên khác chỉ được xem khoản chi.
- Q: Ai được đăng minigame, và Guest có được đọc bài đã công khai không? → A: User đã đăng nhập có thể đăng, sửa và xóa bài của mình; Guest được đọc bài đã công khai.
- Q: Ngoài việc đăng và đọc bài minigame, những chức năng cộng đồng nào cần có trong phiên bản đầu? → A: Có tìm kiếm/lọc, lượt thích, bình luận, yêu thích, báo cáo và kiểm duyệt; Admin có thể ẩn/xóa bài vi phạm; có gợi ý minigame theo Plan.
- Q: Nếu thành viên rời Plan, địa điểm bị xóa hoặc chủ Plan sửa khoản chi, hệ thống cần giữ lịch sử và cập nhật số liệu như thế nào? → A: Không cho rời/xóa thành viên hoặc địa điểm khi còn khoản chi liên quan; phải xử lý khoản chi trước.
- Q: Ai có thể xem một Plan nhóm và cách nào để một User trở thành thành viên của Plan đó? → A: Chủ Plan thêm trực tiếp tài khoản User đã đăng ký; chỉ chủ Plan và thành viên được thêm xem được Plan/chi phí; không chia sẻ công khai.
- Q: Khi một Plan đã có khoản chi, chủ Plan nên được phép xóa Plan theo cách nào? → A: Cho xóa Plan và xóa toàn bộ khoản chi liên quan sau khi xác nhận rõ.
- Q: Ngoài việc kiểm duyệt bài minigame đã xác nhận, Admin có cần quyền nghiệp vụ nào khác trong phiên bản đầu không? → A: Admin được tạo, xem, sửa và xóa mọi Plan và khoản chi, ngoài quyền kiểm duyệt minigame.
- Q: Hệ thống có cần ghi nhật ký khi Admin thao tác với Plan và khoản chi không? → A: Ghi người thực hiện, thời điểm, thao tác tạo/sửa/xóa và Plan/khoản chi bị tác động; không ghi mọi lần xem.
- Q: Một Plan có thể có nhiều điểm đến không, và phiên bản đầu có cần lịch trình hoạt động theo từng ngày không? → A: Mỗi Plan có một điểm đến và có lịch trình hoạt động theo ngày.
- Q: Đề cương IS207 hoặc giảng viên có yêu cầu bắt buộc dùng jQuery AJAX, hay nhóm được dùng Fetch API cho các yêu cầu trao đổi dữ liệu bất đồng bộ? → A: Cả jQuery AJAX và Fetch API đều được chấp nhận.
- Q: Sự kiện nào nên kích hoạt email trong phiên bản đầu, và email đó gửi cho ai? → A: Gửi email khôi phục mật khẩu cho người yêu cầu, thông báo thêm thành viên Plan cho User được thêm, và thông báo xóa Plan cho chủ Plan cùng các thành viên.
- Q: Nếu dịch vụ email tạm thời không gửi được một trong các email đã xác nhận, hệ thống nên xử lý việc thử lại như thế nào? → A: Tự thử lại tối đa 3 lần khi lỗi tạm thời; nếu vẫn lỗi thì báo thất bại.
- Q: Email thêm thành viên và xóa Plan nên tiết lộ những thông tin nào ngoài sự kiện và tên Plan? → A: Chỉ nêu sự kiện và tên Plan; email khôi phục mật khẩu chỉ chứa liên kết khôi phục và hướng dẫn, không kèm dữ liệu Plan hay chi phí.
- Q: Điểm đến của mỗi Plan nên được nhập tự do hay chọn từ danh mục địa điểm dùng chung? → A: Chọn một điểm đến từ kết quả tìm kiếm công khai; kết quả do dịch vụ bên ngoài cung cấp.
- Q: Ai nên cung cấp và cập nhật danh mục điểm đến dùng chung mà User tìm kiếm khi tạo Plan? → A: Dùng dịch vụ bên ngoài để tìm địa điểm; chỉ lưu lựa chọn vào Plan.
- Q: Ngoài tổng chi theo Plan, tổng đã thanh toán và phần chịu của từng thành viên, cần thêm dạng thống kê nào trong phiên bản đầu? → A: Bổ sung thống kê khoản chi theo loại chi.
- Q: Loại chi cho thống kê nên dùng danh mục cố định, hay User được tự tạo loại chi? → A: User nhập tên loại chi tự do trên từng khoản chi.
- Q: Mỗi khoản chi có bắt buộc phải có tên loại chi để thống kê không? → A: Có thể để trống; thống kê gom khoản thiếu nhãn vào nhóm “Chưa phân loại”.
- Q: Nếu hai nhãn loại chi chỉ khác chữ hoa/thường hoặc khoảng trắng đầu/cuối, thống kê có nên gộp chúng thành cùng một loại không? → A: Không tự gộp; khi phát hiện nhãn gần trùng, yêu cầu User chọn lại nhãn đã có trước khi lưu.
- Q: Khi User chọn một điểm đến từ dịch vụ tìm kiếm bên ngoài, thông tin nào cần được lưu cùng Plan? → A: Lưu mã địa điểm do nhà cung cấp trả về cùng tên hiển thị và địa chỉ/tọa độ nếu kết quả cung cấp.
- Q: Bản in trong phiên bản đầu nên bao gồm những nội dung nào? → A: Tóm tắt Plan gồm thông tin chuyến đi, lịch trình theo ngày và tổng hợp chi phí.
- Q: Các khoản chi trong phiên bản đầu dùng một loại tiền tệ hay cần hỗ trợ nhiều loại tiền tệ? → A: Chỉ dùng VND; không quy đổi tiền tệ.
- Q: Khi chia đều một khoản VND không chia hết cho số thành viên, phần dư nên được xử lý thế nào? → A: Tổng phần chịu không được lệch số tiền khoản chi; chia phần dư theo thứ tự thành viên trong Plan.
- Q: Nếu dịch vụ tìm kiếm điểm đến bên ngoài không khả dụng, User nên có thể làm gì để tiếp tục tạo hoặc xem Plan? → A: Cho phép nhập điểm đến thủ công làm phương án dự phòng; thông tin nhà cung cấp chỉ có khi được chọn từ kết quả dịch vụ.
- Q: Khi một khoản chi mới giống khoản đã có về Plan, ngày, số tiền, người thanh toán và địa điểm, hệ thống nên xử lý thế nào? → A: Cảnh báo khoản có thể trùng nhưng vẫn cho lưu sau khi người dùng xác nhận.
- Q: Với khoản chi dùng VND, phần chịu do người dùng nhập tùy chỉnh có được phép chứa phần lẻ của một đồng không? → A: Không; phần chịu tùy chỉnh chỉ chấp nhận số nguyên VND.
- Q: Mỗi hoạt động trong lịch trình theo ngày cần có những thông tin nào trong phiên bản đầu? → A: Tên hoạt động, giờ bắt đầu/kết thúc, địa điểm, hướng dẫn và ghi chú.
- Q: Trong cùng một ngày, các hoạt động của Plan nên được sắp xếp theo quy tắc nào? → A: Mặc định theo giờ bắt đầu, giờ trùng giữ thứ tự đã thêm; User có thể chuyển sang tự sắp xếp và giữ thứ tự thủ công đến khi chọn lại sắp xếp theo giờ.
- Q: Minigame muốn được lọc theo “thể loại” thì mỗi bài nên chọn thể loại theo cách nào? → A: Chọn thể loại từ danh mục thống nhất và có thể thêm từ khóa tự do.
- Q: Chi phí cá nhân có thể được ghi độc lập ngoài Plan nhóm không, hay mọi khoản chi đều phải thuộc một Plan? → A: Mọi khoản chi đều thuộc một Plan; chi phí cá nhân được ghi trong Plan riêng có thể chỉ gồm chủ sở hữu.
- Q: Người thanh toán một khoản chi trong Plan nhóm có bắt buộc là thành viên Plan đó không? → A: Có; người thanh toán phải là thành viên hiện tại của Plan.
- Q: Những thông tin nào phải có để User đăng một bài minigame? → A: Tên và luật/hướng dẫn chơi là bắt buộc; mô tả, ảnh, số người chơi, thời lượng, dụng cụ và thể loại có thể để trống.
- Q: Sau khi User đăng bài minigame hợp lệ, bài đó nên được Guest xem ngay hay phải chờ Admin duyệt? → A: Bài phải chờ Admin duyệt trước khi công khai.
- Q: Khi Admin tạo Plan, ai nên là chủ sở hữu của Plan đó? → A: Admin trở thành chủ sở hữu của Plan mình tạo.

## 2. Người dùng và phạm vi quyền

| Vai trò | Mục đích | Phạm vi đã xác nhận | Quyền/chức năng chưa được suy diễn |
|---|---|---|---|
| Guest | Khách chưa xác thực. | Có thể đọc bài minigame đã công khai; có thể tiếp cận luồng công khai và đăng ký/đăng nhập. | Không được xem dữ liệu riêng tư hoặc thực hiện thao tác được bảo vệ; không được tạo/sửa/xóa bài minigame. |
| User | Người dùng thông thường đã xác thực. | Quản lý Plan của mình; nếu được chủ Plan thêm trực tiếp vào Plan nhóm thì chỉ có quyền xem khoản chi trong Plan đó. | Không xem Plan nếu không phải chủ/thành viên; không được sửa khoản chi của Plan nhóm; không có chia sẻ Plan công khai hoặc thanh toán/đối soát. |
| Admin | Quản trị viên hệ thống. | Có thể tạo, xem, sửa và xóa mọi Plan/khoản chi; xử lý báo cáo và ẩn/xóa bài minigame vi phạm; thao tác tạo/sửa/xóa Plan hoặc khoản chi được ghi nhật ký. | Không mặc định quyền quản trị tài khoản User hoặc nội dung khác ngoài phạm vi đã nêu. |

Mọi thao tác với dữ liệu riêng tư phải xác thực người dùng và kiểm tra quyền ở
phía hệ thống. User chỉ được truy cập tài nguyên của mình, trừ khi có quy tắc
chia sẻ/cộng tác được phê duyệt và đặc tả riêng. Khi chưa có yêu cầu quyền rõ
ràng, thao tác bị từ chối theo nguyên tắc mặc định an toàn.

## 3. Phạm vi sản phẩm

### 3.1. Nghĩa vụ học phần bắt buộc

Các mục dưới đây là nghĩa vụ của đồ án, không phải bằng chứng rằng chức năng đã
được triển khai:

- Khảo sát website/ứng dụng thực tế liên quan đến du lịch; phân tích chức năng,
  nhu cầu và ý tưởng phù hợp; không sao chép giao diện hoặc cách triển khai.
- Phân tích vai trò và chức năng tương ứng.
- Phân tích, thiết kế cơ sở dữ liệu; ERD, khóa, quan hệ, ràng buộc, dữ liệu mẫu
  và lập luận chuẩn hóa hướng 3NF.
- Sử dụng UML thích hợp, gắn với yêu cầu hoặc hành vi thực tế.
- Thiết kế UI/UX phù hợp chức năng bằng Figma; đồng bộ thiết kế và sản phẩm;
  hỗ trợ desktop/mobile cùng các trạng thái giao diện.
- Thể hiện các công nghệ và kiến thức web bắt buộc được liệt kê trong đề cương,
  bao gồm HTML/CSS, JavaScript, PHP, MySQL, CodeIgniter, MVC, client-server,
  thao tác dữ liệu và triển khai/vận hành.
- Cung cấp các năng lực tìm kiếm, hiển thị dữ liệu, thêm, sửa, xóa, thống kê,
  in ấn và gửi email thông qua các quy trình có ý nghĩa đối với sản phẩm.
- Kiểm thử, tài liệu kỹ thuật, triển khai server và báo cáo đồ án.

Việc thư viện hiện diện trong repository không tự chứng minh yêu cầu AJAX đã
được đáp ứng; cần có luồng bất đồng bộ có ý nghĩa đối với sản phẩm và bằng
chứng kiểm thử. Theo xác nhận của nhóm, cả jQuery AJAX và Fetch API đều được
chấp nhận.

### 3.2. Yêu cầu sản phẩm đã xác nhận

| ID | Yêu cầu/định hướng | Căn cứ |
|---|---|---|
| PRD-001 | Sản phẩm thuộc lĩnh vực lập kế hoạch du lịch. | README và tên/mô tả dự án. |
| PRD-002 | Sản phẩm hướng đến quản lý chi tiêu cá nhân và nhóm. | README dự án. |
| PRD-003 | Các vai trò được xác lập là Guest, User, Admin. | Project Constitution và yêu cầu người dùng. |
| PRD-004 | Quyết định công nghệ/kiến trúc đã thống nhất phải được giữ nguyên trừ khi có phê duyệt thay đổi. | Project Constitution. |
| PRD-005 | Người dùng yêu cầu đưa quản lý/chia chi phí trong Plan và cộng đồng bài đăng thông tin minigame vào phạm vi rà soát sản phẩm. | Bổ sung phạm vi nghiệp vụ trong phiên Clarify 2026-11-02; quyết định đã chốt được ghi trong Clarifications, chi tiết còn mở liệt kê tại §12. |

Các yêu cầu PRD xác nhận mục tiêu và ranh giới ban đầu. Luồng CRUD Plan, chia sẻ
Plan riêng tư, lịch trình, chi phí, cộng đồng minigame và quyền Admin đã được
làm rõ tại Clarifications và các yêu cầu chức năng bên dưới; các trường hợp còn
mở được giữ riêng trong §12, không làm mất các quyết định đã chốt.

### 3.3. Đề xuất luồng sản phẩm phiên bản đầu

Các luồng sau là đề xuất cụ thể hóa mục tiêu; nhóm cần duyệt trước khi coi là
phạm vi thực thi:

| Mã | Đề xuất | Giá trị | Trạng thái |
|---|---|---|---|
| PROP-001 | User tạo, xem, cập nhật, xóa kế hoạch của mình; kế hoạch có một điểm đến chọn từ kết quả dịch vụ bên ngoài hoặc nhập thủ công làm phương án dự phòng, khoảng ngày và lịch trình hoạt động theo ngày. | Cung cấp luồng lập kế hoạch có thể trình diễn và kiểm thử. | Quyền và luồng chính đã được xác nhận; nhà cung cấp/API cụ thể còn cần xác định. |
| PROP-002 | Mọi khoản chi thuộc một Plan; chi phí cá nhân được ghi trong Plan riêng có thể chỉ gồm chủ sở hữu. Với Plan nhóm, chủ Plan quản lý thành viên và ghi từng khoản chi; mỗi khoản có một người thanh toán và phần chịu của từng thành viên có thể chia đều hoặc tùy chỉnh; hệ thống tổng hợp đã trả và phần chịu nhưng không ghi nhận hoàn trả. | Gắn yêu cầu chi tiêu cá nhân/nhóm với mục đích sản phẩm. | Quyền xem/sửa, cách thêm thành viên, mô hình phân bổ, VND, cảnh báo khoản có thể trùng, quy tắc xóa liên quan và phạm vi chi phí cá nhân trong Plan đã làm rõ. |
| PROP-003 | User tìm kiếm kế hoạch của mình theo tên/điểm đến và xem danh sách/chi tiết. | Ánh xạ yêu cầu tìm kiếm/hiển thị vào dữ liệu sản phẩm. | Đề xuất; cần xác nhận tiêu chí và bộ lọc. |
| PROP-004 | User xem tổng chi phí theo kế hoạch và in tóm tắt Plan gồm thông tin chuyến đi, lịch trình theo ngày và tổng hợp chi phí. | Ánh xạ thống kê/in ấn vào quy trình hữu ích. | Nội dung bản in và sử dụng VND đã xác nhận. |
| PROP-005 | User ghi và xem lịch trình hoạt động được nhóm theo ngày trong Plan. | Cụ thể hóa quy trình lập kế hoạch chuyến đi. | Đã xác nhận lịch trình theo ngày; hoạt động có tên, giờ bắt đầu/kết thúc, địa điểm, hướng dẫn và ghi chú. Mặc định sắp xếp theo giờ bắt đầu, giờ trùng giữ thứ tự thêm; User có thể chuyển sang sắp xếp thủ công. |
| PROP-006 | User tạo và khám phá bài đăng chia sẻ thông tin minigame phù hợp để tham khảo khi đi cùng bạn bè; minigame không được chơi trực tuyến trên website. | Bổ sung sản phẩm được người dùng yêu cầu rà soát. | Vai trò, tìm/lọc, tương tác, báo cáo, kiểm duyệt, quy tắc thể loại, trường bắt buộc và yêu cầu Admin duyệt trước khi công khai đã được xác nhận; chi tiết xử lý khi sửa bài đã công khai còn cần làm rõ trước triển khai. |

### 3.4. Phân loại phạm vi

| Mức | Chức năng | Căn cứ và giới hạn |
|---|---|---|
| Mandatory | Nghĩa vụ IS207 được liệt kê tại §3.1: khảo sát, phân tích vai trò, ERD/3NF, UML, UI/UX, công nghệ, CRUD, tìm kiếm, hiển thị, thống kê, in, email, kiểm thử, triển khai và báo cáo. | Bắt buộc theo yêu cầu học phần được người dùng cung cấp; từng chức năng phải gắn với workflow có ý nghĩa. |
| Core | Lập kế hoạch du lịch một điểm đến, có lịch trình hoạt động theo ngày, và quản lý/chia chi phí cá nhân/nhóm trong Plan. Mọi khoản chi thuộc một Plan; chi phí cá nhân thuộc Plan riêng có thể chỉ có chủ sở hữu. | README và phạm vi nghiệp vụ được người dùng xác nhận; Q-016. Chủ Plan thêm trực tiếp User đã đăng ký; chỉ chủ Plan và thành viên được thêm xem Plan/chi phí nhóm. Mô hình chi phí và chính sách xóa đã làm rõ. |
| Core | Admin quản lý mọi Plan và khoản chi; đồng thời kiểm duyệt bài minigame vi phạm. | Người dùng đã xác nhận quyền tạo/xem/sửa/xóa mọi Plan và khoản chi; không bao gồm tự động quản lý tài khoản User hoặc nội dung khác. |
| Core | Cộng đồng chia sẻ bài đăng thông tin minigame để khám phá và tham khảo khi du lịch nhóm. | User đã đăng nhập đăng/sửa/xóa bài của mình; tên và luật/hướng dẫn chơi là bắt buộc, các trường khác tùy chọn; bài chờ Admin duyệt trước khi Guest đọc được; Admin xử lý báo cáo và có thể giữ, ẩn hoặc xóa bài vi phạm. Chi tiết xử lý khi sửa bài đã công khai cần chốt trước triển khai. Chỉ là nội dung thông tin. |
| Core | Tìm kiếm/lọc minigame; lượt thích, bình luận, yêu thích; báo cáo và kiểm duyệt (Admin ẩn/xóa bài vi phạm); gợi ý minigame theo Plan. | Người dùng đã xác nhận nhóm chức năng phiên bản đầu; thể loại dùng danh mục thống nhất kèm từ khóa tự do tùy chọn. Chi tiết gợi ý, bình luận và xử lý báo cáo vẫn cần đặc tả. |
| Optional | Lưu/liên kết bài minigame vào Plan như nội dung tham khảo; đây không phải khởi tạo phiên chơi. | Chưa được xác nhận và không nằm trong lựa chọn chức năng cộng đồng phiên bản đầu. |
| Out of Scope | Chơi minigame trực tuyến, phòng chơi, xử lý lượt chơi hoặc trạng thái trận; cổng thanh toán, giao dịch tiền thật/hoàn tiền tự động cho chi phí. | Người dùng loại trừ rõ ràng; Plan chỉ có thể lưu/tham khảo minigame nếu sau này được duyệt. |

### 3.5. Ngoài phạm vi hiện tại và định hướng tương lai

- Đăng nhập Google/Facebook: hướng tương lai, chưa được phê duyệt/triển khai.
- Đặt chỗ thương mại, thanh toán trực tuyến, tích hợp booking bên ngoài: ngoài
  phạm vi cho đến khi có yêu cầu riêng; không cần thiết để đáp ứng mô tả hiện có.
- Mạng xã hội, bảng tin, đánh giá công khai, chat và chia sẻ Plan công khai:
  chưa được xác nhận, ngoại trừ phạm vi bài đăng minigame được phân loại tại
  §3.4. Thành viên Plan riêng tư được chủ Plan thêm trực tiếp theo quy tắc tại
  §4, User Story 3.
- Chia tiền, quản lý công nợ, hoàn ứng, thanh toán giữa các thành viên: không
  suy ra chỉ từ yêu cầu ghi nhận chi tiêu nhóm; chỉ ghi nhận phần chịu và chênh
  lệch theo Q1.
- Admin quản lý tài khoản User hoặc nội dung khác ngoài Plan, khoản chi và
  kiểm duyệt minigame: chưa được xác nhận.
- Chơi minigame trực tuyến, tạo phòng, xử lý lượt chơi hoặc lưu trạng thái ván
  chơi: ngoài phạm vi theo yêu cầu người dùng.

## 4. Hành trình và kịch bản người dùng

### User Story 1 — Truy cập và xác thực (Ưu tiên: P1, đề xuất)

Là Guest, tôi muốn đăng ký hoặc đăng nhập để truy cập chức năng quản lý dữ liệu
cá nhân.

**Giá trị**: Tạo ranh giới rõ ràng giữa thông tin công khai và dữ liệu riêng tư.

**Kiểm thử độc lập**: Guest đăng ký/đăng nhập với dữ liệu hợp lệ; sau xác thực
thành công có thể vào khu vực được bảo vệ; thông tin sai hoặc thiếu không được
cấp quyền.

**Tiêu chí chấp nhận**:

1. **Given** người chưa xác thực, **When** mở chức năng riêng tư, **Then** hệ
   thống yêu cầu xác thực và không tiết lộ dữ liệu riêng tư.
2. **Given** dữ liệu đăng ký/đăng nhập không hợp lệ, **When** gửi biểu mẫu,
   **Then** người dùng nhận thông báo có thể hiểu và không nhận được quyền truy
   cập.
3. **Given** đăng nhập thành công, **When** người dùng mở khu vực cá nhân,
   **Then** hệ thống chỉ hiển thị tài nguyên được phép truy cập.

**Ghi chú phạm vi**: Khôi phục mật khẩu qua email thuộc phạm vi email đã xác
nhận. Cách xác minh địa chỉ, vòng đời token, lưu trữ thông tin xác thực và đăng
xuất cần được chốt trong thiết kế phù hợp; đặc tả không chọn phương án lưu
token.

### User Story 2 — Quản lý kế hoạch du lịch cá nhân (Ưu tiên: P1, luồng cốt lõi đã xác nhận)

Là User, tôi muốn tạo và quản lý kế hoạch tại một điểm đến cùng lịch trình hoạt
động theo ngày để tổ chức chuyến đi.

**Giá trị**: Cụ thể hóa mục tiêu lập kế hoạch du lịch mà không thêm booking hoặc
dịch vụ thương mại.

**Kiểm thử độc lập**: Tạo kế hoạch có dữ liệu hợp lệ, xem lại, cập nhật, tìm
kiếm và xóa kế hoạch; tài khoản khác không đọc hoặc sửa được kế hoạch riêng tư.

**Tiêu chí chấp nhận**:

1. **Given** User đã xác thực, **When** tạo kế hoạch với các trường bắt buộc hợp
   lệ và chọn một điểm đến từ kết quả dịch vụ tìm kiếm bên ngoài, **Then** kế
   hoạch được lưu và xuất hiện trong danh sách của chính User.
2. **Given** ngày kết thúc sớm hơn ngày bắt đầu, **When** lưu kế hoạch, **Then**
   hệ thống từ chối dữ liệu và chỉ rõ trường cần sửa.
3. **Given** User mở kế hoạch của mình, **When** cập nhật thông tin hợp lệ,
   **Then** nội dung mới được hiển thị khi xem lại.
4. **Given** User là chủ Plan có khoản chi, **When** yêu cầu xóa và xác nhận
   rõ tác động, **Then** Plan cùng toàn bộ khoản chi và phần chịu liên quan bị
   xóa; dữ liệu đó không còn xuất hiện trong danh sách/tìm kiếm.
5. **Given** User khác biết mã/đường dẫn tài nguyên riêng tư, **When** yêu cầu
   xem hoặc sửa tài nguyên đó, **Then** hệ thống từ chối mà không tiết lộ dữ
   liệu.
6. **Given** User thêm hoạt động vào lịch trình, **When** chọn ngày trong Plan,
   **Then** hoạt động được gắn với ngày đó và hiển thị trong nhóm lịch trình
   tương ứng.
7. **Given** một ngày có nhiều hoạt động, **When** xem lịch trình, **Then**
   hệ thống mặc định sắp xếp theo giờ bắt đầu, giữ thứ tự thêm khi giờ trùng;
   User có thể chuyển sang sắp xếp thủ công và giữ thứ tự đó đến khi chọn lại
   sắp xếp theo giờ.

**Trường dữ liệu tạm đề xuất**: tên kế hoạch, điểm đến được chọn từ dịch vụ
(mã nhà cung cấp, tên hiển thị và địa chỉ/tọa độ nếu kết quả có) hoặc nhập thủ
công (tên User nhập, không có mã nhà cung cấp), ngày bắt đầu, ngày kết thúc,
mô tả tùy chọn và các hoạt động được nhóm theo ngày. Mỗi hoạt động có tên,
giờ bắt đầu/kết thúc, địa điểm, hướng dẫn và ghi chú. Đây chưa phải ERD hoặc
danh sách trường vật lý đã phê duyệt.

### User Story 3 — Ghi nhận chi phí trong Plan (Ưu tiên: P1, luồng cốt lõi đã xác nhận)

Là chủ Plan, tôi muốn ghi nhận từng khoản chi, người thanh toán và phần chịu
của thành viên để theo dõi chi phí chuyến đi hoặc chi phí cá nhân trong Plan
riêng của mình. Mọi khoản chi đều phải thuộc một Plan; trong Plan nhóm, người
thanh toán phải là thành viên hiện tại của Plan.

**Giá trị**: Ánh xạ trực tiếp định hướng quản lý chi tiêu cá nhân/nhóm vào một
quy trình có thể kiểm thử.

**Kiểm thử độc lập**: Chủ Plan ghi khoản chi hợp lệ, xem tổng, sửa và xóa khoản
chi; thành viên khác xem được nhưng không thể thay đổi khoản chi; người ngoài
Plan không thể truy cập dữ liệu riêng tư.

**Tiêu chí chấp nhận**:

1. **Given** User là chủ Plan, **When** ghi khoản chi với số
   tiền dương và thông tin bắt buộc, **Then** khoản chi xuất hiện trong đúng
   ngữ cảnh kế hoạch.
2. **Given** số tiền thiếu, không phải số hợp lệ hoặc không dương, **When** lưu
   khoản chi, **Then** hệ thống từ chối và thông báo lỗi phù hợp.
3. **Given** chủ Plan cập nhật hoặc xóa khoản chi, **When** xem lại danh
   sách/tổng, **Then** số liệu phản ánh dữ liệu còn hiệu lực.
4. **Given** thành viên khác trong Plan, **When** xem khoản chi, **Then** hệ
   thống cho phép đọc; **When** thành viên thử thêm, sửa hoặc xóa khoản chi,
   **Then** hệ thống từ chối thay đổi.
5. **Given** User không thuộc Plan, **When** yêu cầu đọc, thêm, sửa hoặc xóa
   khoản chi, **Then** hệ thống từ chối mà không tiết lộ dữ liệu.
6. **Given** một Plan có các khoản chi còn hiệu lực, **When** xem tổng hợp,
   **Then** tổng Plan và số tiền thanh toán/phần chịu theo thành viên phản ánh
   đúng các bản ghi và được hiển thị bằng VND.
7. **Given** khoản chi có một người thanh toán và phần chịu được phân bổ hợp lệ,
   **When** thành viên xem bảng tổng hợp, **Then** mỗi dòng đại diện một thành
   viên, mỗi cột đại diện một địa điểm, và ô thể hiện tổng tiền thành viên đó
   đã thanh toán tại địa điểm; tổng phần chịu và chênh lệch được trình bày riêng.
8. **Given** phần chịu tùy chỉnh không cộng đúng số tiền khoản chi, **When**
   người dùng lưu, **Then** hệ thống từ chối lưu và nêu số tiền còn thiếu hoặc
   vượt quá.
9. **Given** thành viên hoặc địa điểm đang được khoản chi tham chiếu, **When**
   có yêu cầu xóa thành viên/rời Plan hoặc xóa địa điểm, **Then** hệ thống từ
   chối và yêu cầu xử lý các khoản chi liên quan trước, không xóa dữ liệu dây
   chuyền.
10. **Given** chủ Plan thêm trực tiếp một User đã đăng ký vào Plan, **When**
    User đó truy cập Plan, **Then** User được xem Plan và khoản chi nhưng không
    được thêm, sửa hoặc xóa khoản chi; User ngoài Plan bị từ chối truy cập.
11. **Given** User nhập tên loại chi chỉ khác chữ hoa/thường hoặc khoảng trắng
    đầu/cuối với nhãn đã có, **When** lưu khoản chi, **Then** hệ thống yêu cầu
    chọn nhãn đã có thay vì lưu một biến thể trùng.
12. **Given** người được chọn thanh toán không phải thành viên hiện tại của Plan
    nhóm, **When** chủ Plan lưu khoản chi, **Then** hệ thống từ chối khoản chi
    và yêu cầu chọn một thành viên Plan.

**Quy tắc đã làm rõ**: Tất cả khoản chi sử dụng VND; không hỗ trợ tỷ giá hoặc
quy đổi tiền tệ. Khi chia đều có phần dư, phân bổ phần dư lần lượt theo thứ tự
thành viên trong Plan để tổng phần chịu luôn bằng số tiền khoản chi. Phần chịu
tùy chỉnh là số nguyên VND. Mỗi khoản chi có một người thanh toán và có thể
phân bổ phần chịu cho nhiều thành viên. Cách phân bổ được chọn cho từng khoản:
chia đều hoặc nhập số tiền tùy chỉnh. Hệ thống tính tổng tiền đã thanh toán và
phần mỗi người chịu để thể hiện chênh lệch; không ghi nhận khoản hoàn trả hoặc
thực hiện giao dịch tiền thật. Nhiều khoản chi có thể cùng gắn với một địa
điểm. Bảng thành viên × địa điểm là tổng hợp từ các bản ghi khoản chi, không
thay thế chứng từ từng khoản.

**Quy tắc đã làm rõ**: Mọi khoản chi thuộc một Plan. Chi phí cá nhân được ghi
trong Plan riêng, có thể chỉ gồm chủ sở hữu; không có khoản chi độc lập ngoài
Plan. Trong Plan nhóm, người thanh toán phải là thành viên hiện tại của Plan.

**Quy tắc đã làm rõ**: Mỗi khoản chi có thể có tên loại chi do User nhập tự do
hoặc để trống; thống kê gom khoản thiếu nhãn vào nhóm “Chưa phân loại”. Nếu tên
nhập chỉ khác chữ hoa/thường hoặc khoảng trắng đầu/cuối với nhãn đã có, User
phải chọn nhãn hiện có trước khi lưu; hệ thống không tự gộp biến thể.

**Quy tắc đã làm rõ**: User tạo Plan là chủ sở hữu; khi Admin tạo Plan, Admin
là chủ sở hữu. Admin vẫn có quyền quản lý mọi Plan theo phạm vi đã được xác nhận.

### User Story 4 — Tìm kiếm và xem tổng hợp (Ưu tiên: P2, đề xuất)

Là User, tôi muốn tìm kế hoạch và xem tổng chi phí để nhanh chóng xem lại thông
tin chuyến đi.

**Giá trị**: Ánh xạ yêu cầu học phần về tìm kiếm, hiển thị và thống kê vào dữ
liệu sản phẩm.

**Kiểm thử độc lập**: Tìm kế hoạch của User, mở chi tiết, đối chiếu tổng với
các khoản chi còn hiệu lực và nhận kết quả rỗng khi không có kết quả.

**Tiêu chí chấp nhận**:

1. **Given** User có kế hoạch phù hợp, **When** tìm theo tiêu chí được hỗ trợ,
   **Then** kết quả chỉ chứa kế hoạch được User phép xem.
2. **Given** không có kết quả phù hợp, **When** tìm kiếm, **Then** hệ thống hiển
   thị trạng thái rỗng có hướng dẫn thay đổi tiêu chí.
3. **Given** một kế hoạch có các khoản chi hợp lệ, **When** mở tổng hợp,
   **Then** tổng bằng tổng các khoản chi được tính theo quy tắc đã công bố.
4. **Given** User yêu cầu bản in, **When** chọn in tóm tắt, **Then** nội dung
   in gồm thông tin chuyến đi, lịch trình theo ngày và tổng hợp chi phí, chỉ
   bao gồm dữ liệu được phép xem và có thể đọc được trên giấy.
5. **Given** các khoản chi hợp lệ có tên loại hoặc để trống, **When** User xem
   thống kê, **Then** tổng theo từng loại khớp với các khoản chi thuộc loại đó
   và khoản thiếu tên được gom vào nhóm “Chưa phân loại”.

### User Story 5 — Guest khám phá và bắt đầu sử dụng (Ưu tiên: P2, đề xuất)

Là Guest, tôi muốn hiểu mục đích dịch vụ và tìm đường đến đăng ký/đăng nhập để
quyết định có sử dụng hay không.

**Giá trị**: Cung cấp điểm bắt đầu rõ ràng mà không giả định có danh mục điểm
đến công khai.

**Kiểm thử độc lập**: Guest mở trang công khai, nhận thông tin mô tả dịch vụ và
có thể vào luồng xác thực; không thể xem dữ liệu riêng tư.

**Tiêu chí chấp nhận**:

1. **Given** Guest mở trang công khai, **When** trang tải, **Then** nội dung mô
   tả đúng mục đích sản phẩm và không khẳng định tính năng chưa có.
2. **Given** Guest chọn đăng ký/đăng nhập, **When** thao tác, **Then** hệ thống
   đưa Guest đến đúng luồng xác thực.

### Vai trò Admin

Admin được tạo, xem, sửa và xóa mọi Plan/khoản chi, ngoài việc xem báo cáo và
ẩn/xóa bài minigame vi phạm. Admin không mặc định có quyền quản lý tài khoản
User hoặc nội dung khác. Mỗi thao tác tạo/sửa/xóa Plan hoặc khoản chi của Admin
phải được ghi nhật ký gồm người thực hiện, thời điểm, loại thao tác và bản ghi
bị tác động; việc chỉ xem dữ liệu không tạo nhật ký thao tác.

### User Story 6 — Chia sẻ và khám phá bài đăng minigame (Ưu tiên: P2, phạm vi cốt lõi)

Là thành viên cộng đồng Cùng Đi, tôi muốn đăng hoặc khám phá thông tin về
minigame để tham khảo trò chơi phù hợp cho chuyến đi cùng bạn bè.

**Giá trị**: Cung cấp nội dung hữu ích cho du lịch nhóm mà không xây dựng trò
chơi trực tuyến.

**Kiểm thử độc lập**: User đã đăng nhập tạo/sửa/xóa bài của mình; bài mới chỉ
hiển thị với Guest sau khi Admin duyệt; hệ thống không khởi tạo phòng hay phiên
chơi.

**Tiêu chí chấp nhận**:

1. **Given** User đã đăng nhập gửi bài với tên và luật/hướng dẫn chơi hợp lệ,
   **When** tạo bài, **Then** bài được lưu cùng tác giả và thời điểm trong
   trạng thái chờ Admin duyệt, chưa hiển thị với Guest.
2. **Given** Guest mở một bài đã công khai, **When** đọc bài, **Then** thông tin
   minigame và nội dung luật chơi được trình bày rõ
   ràng, cùng thông tin số người/thời lượng/dụng cụ nếu đã cung cấp.
3. **Given** bài thiếu tên hoặc luật/hướng dẫn chơi, hoặc có ảnh được cung cấp
   nhưng không hợp lệ, **When** gửi bài, **Then** hệ thống từ chối và thông báo
   cách sửa.
4. **Given** User đã đăng nhập sửa/xóa bài do mình tạo, **When** thao tác hợp
   lệ, **Then** chỉ bài của User đó bị thay đổi.
5. **Given** người dùng truy cập bài chưa công khai hoặc sửa/xóa bài của người
   khác, **When** thao tác, **Then** hệ thống từ chối và không lộ dữ liệu riêng.
6. **Given** người dùng đọc bài minigame, **When** chọn hướng dẫn, **Then** chỉ
   xem nội dung hướng dẫn; website không tạo phòng, lượt chơi hoặc xử lý trò chơi.

7. **Given** User đã đăng nhập, **When** thích hoặc lưu yêu thích một bài,
   **Then** trạng thái tương ứng được ghi nhận; thao tác lặp không tạo lượt
   thích/yêu thích trùng.
8. **Given** User đã đăng nhập gửi bình luận hợp lệ, **When** mở bài đăng,
   **Then** bình luận được gắn với tác giả và thời điểm; nội dung sai/thiếu bị
   từ chối.
9. **Given** User đã đăng nhập báo cáo một bài, **When** Admin kiểm duyệt báo cáo,
   **Then** Admin có thể giữ nguyên hoặc ẩn/xóa bài vi phạm; nội dung đã ẩn
   không còn xuất hiện với Guest.
10. **Given** người dùng tìm hoặc lọc theo số người, thời lượng hoặc thể loại,
    **When** có bài phù hợp, **Then** kết quả chỉ chứa bài thỏa điều kiện;
    thể loại được chọn từ danh mục thống nhất và có thể kèm từ khóa tự do.
11. **Given** Plan có số thành viên xác định, **When** người dùng xem gợi ý,
    **Then** hệ thống gợi ý bài phù hợp số người; gợi ý không tự thêm minigame
    vào Plan hoặc mở trò chơi.

**Thông tin bài đăng ứng viên**: tên minigame, ảnh minh họa, mô tả, hướng dẫn/
luật chơi, số người phù hợp, thời lượng, dụng cụ cần chuẩn bị, thể loại chọn từ
danh mục thống nhất, từ khóa tự do tùy chọn, người đăng và thời gian đăng. User
đã đăng nhập quản lý bài của mình; Guest đọc bài công khai.
Phiên bản đầu gồm tìm kiếm/lọc, lượt thích, bình luận, yêu thích, báo cáo, Admin
ẩn/xóa bài vi phạm và gợi ý minigame theo số thành viên Plan. Minigame vẫn chỉ
là nội dung tham khảo; gợi ý không tự thêm bài vào Plan hay bắt đầu phiên chơi.

### User Story 7 — Quản trị Plan, chi phí và nội dung vi phạm (Ưu tiên: P1, đã xác nhận)

Là Admin, tôi muốn quản lý mọi Plan và khoản chi, đồng thời xử lý báo cáo
minigame để duy trì tính toàn vẹn dữ liệu và kiểm duyệt nội dung vi phạm.

**Kiểm thử độc lập**: Admin tạo/xem/sửa/xóa Plan và khoản chi; các thao tác
ghi dữ liệu được audit; Admin xem báo cáo và giữ nguyên, ẩn hoặc xóa bài vi
phạm. Guest không còn thấy bài đã ẩn.

**Tiêu chí chấp nhận**:

1. **Given** Admin đã xác thực, **When** tạo, xem, sửa hoặc xóa bất kỳ Plan
   hay khoản chi nào, **Then** thao tác được cho phép và tuân thủ các quy tắc
   toàn vẹn dữ liệu, bao gồm xác nhận và xóa dữ liệu liên quan khi áp dụng;
   Plan do Admin tạo có Admin đó làm chủ sở hữu.
2. **Given** Admin tạo, sửa hoặc xóa Plan/khoản chi, **When** thao tác hoàn
   tất, **Then** nhật ký ghi người thực hiện, thời điểm, loại thao tác và bản
   ghi bị tác động.
3. **Given** Admin chỉ xem Plan hoặc khoản chi, **When** hoàn tất việc xem,
   **Then** không tạo nhật ký thao tác xem.
4. **Given** Admin xem báo cáo minigame, **When** quyết định xử lý bài vi
   phạm, **Then** Admin có thể giữ nguyên, ẩn hoặc xóa bài; bài đã ẩn không
   xuất hiện với Guest.
5. **Given** bài minigame mới đang chờ duyệt, **When** Admin phê duyệt bài,
   **Then** bài được công khai và có thể được Guest đọc.
6. **Given** Admin yêu cầu quản lý tài khoản User hoặc nội dung khác ngoài
   Plan, khoản chi và kiểm duyệt minigame, **When** thao tác, **Then** quyền
   đó không được suy ra từ phạm vi Admin đã duyệt.

## 5. Yêu cầu

### 5.1. Yêu cầu chức năng

Các yêu cầu có trạng thái **Đề xuất** không được xem là phạm vi được duyệt chỉ
vì chúng có mã FR; cần xác nhận qua Open Questions trước khi lập kế hoạch.

| ID | Yêu cầu chức năng | Trạng thái |
|---|---|---|
| FR-001 | Sản phẩm MUST hỗ trợ mục tiêu lập kế hoạch du lịch và quản lý chi tiêu cá nhân/nhóm. | Đã xác nhận |
| FR-002 | Hệ thống MUST phân biệt Guest, User và Admin; MUST không thêm vai trò khác nếu chưa có phê duyệt. | Đã xác nhận |
| FR-003 | Hệ thống MUST bảo vệ dữ liệu riêng tư bằng xác thực, kiểm tra quyền và kiểm tra quyền sở hữu ở mọi thao tác liên quan. | Đã xác nhận theo Constitution |
| FR-004 | Guest MUST có thể tiếp cận luồng đăng ký/đăng nhập và đọc bài minigame đã công khai; loại nội dung công khai khác cần phê duyệt. | Đã làm rõ một phần |
| FR-005 | User MUST có thể tạo, xem, cập nhật và xóa kế hoạch thuộc quyền sở hữu của mình. | Đã xác nhận |
| FR-006 | Mỗi Plan MUST có đúng một điểm đến, chọn từ dịch vụ tìm kiếm bên ngoài hoặc nhập thủ công khi dịch vụ không khả dụng; lựa chọn từ dịch vụ MUST lưu mã điểm đến do nhà cung cấp trả về cùng tên hiển thị, và địa chỉ/tọa độ nếu có. Plan MUST hỗ trợ lịch trình hoạt động theo ngày; mỗi hoạt động có tên, giờ bắt đầu/kết thúc, địa điểm, hướng dẫn và ghi chú. Mặc định MUST sắp xếp hoạt động theo giờ bắt đầu, giữ thứ tự thêm khi giờ trùng; User MAY chuyển sang thứ tự thủ công và giữ thứ tự đó đến khi chọn lại sắp xếp theo giờ. Hệ thống MUST từ chối khoảng ngày không hợp lệ. Nhà cung cấp cụ thể cần được chốt trước khi duyệt form. | Đã làm rõ một phần |
| FR-007 | Trong Plan nhóm, chủ Plan MUST có thể thêm User đã đăng ký vào danh sách thành viên và thêm, xem, sửa, xóa mọi khoản chi; thành viên được thêm MUST chỉ được xem Plan/chi phí; người ngoài Plan MUST không được truy cập. Plan nhóm MUST NOT được chia sẻ công khai. | Đã làm rõ |
| FR-008 | Với Plan nhóm, mỗi khoản chi MUST có đúng một người thanh toán là thành viên hiện tại của Plan và phần chịu chi cho một hoặc nhiều thành viên; cách phân bổ cho từng khoản MUST là chia đều hoặc số tiền tùy chỉnh nguyên VND. Khi chia đều có phần dư VND, hệ thống MUST phân bổ từng đồng dư theo thứ tự thành viên trong Plan để tổng phần chịu khớp khoản chi. Hệ thống MUST tính chênh lệch nhưng MUST NOT ghi nhận hoàn trả hoặc thực hiện giao dịch tiền thật. | Đã làm rõ |
| FR-009 | Hệ thống MUST tính tổng Plan, tổng đã thanh toán theo thành viên, tổng phần chịu theo thành viên và chênh lệch từ các khoản chi còn hiệu lực bằng VND; phần chịu tùy chỉnh MUST là số nguyên VND và các phần chịu MUST cộng đúng tổng khoản chi. | Core; phép tính và đơn vị tiền tệ đã được xác nhận |
| FR-010 | User MUST có thể tìm kiếm kế hoạch và xem kết quả/chi tiết chỉ trong phạm vi được phép. | Đề xuất |
| FR-011 | Hệ thống MUST hiển thị trạng thái rỗng, tải, thành công và lỗi phù hợp tại các luồng sản phẩm. | Đã xác nhận theo Constitution |
| FR-012 | User MUST có thể in tóm tắt Plan gồm thông tin chuyến đi, lịch trình hoạt động theo ngày và tổng hợp chi phí; chỉ dữ liệu User được phép xem MUST xuất hiện trong bản in. | Nghĩa vụ học phần; nội dung bản in đã làm rõ |
| FR-013 | Hệ thống MUST cung cấp thống kê tổng chi theo Plan, thành viên và loại chi do User nhập tự do trên khoản chi; khoản không có tên loại MUST được gom vào nhóm “Chưa phân loại”; nhãn gần trùng theo chữ hoa/thường hoặc khoảng trắng đầu/cuối MUST được xác nhận bằng chọn lại nhãn hiện có trước khi lưu; tổng theo loại MUST khớp với các khoản chi còn hiệu lực cùng loại. | Nghĩa vụ học phần; nhóm thống kê và quy tắc nhãn đã làm rõ |
| FR-014 | Hệ thống MUST gửi email khôi phục mật khẩu cho người yêu cầu, thông báo thêm thành viên Plan cho User được thêm và thông báo xóa Plan cho chủ Plan cùng các thành viên; email Plan chỉ nêu sự kiện và tên Plan, email khôi phục chỉ chứa liên kết cùng hướng dẫn; khi lỗi tạm thời, MUST tự thử lại tối đa 3 lần rồi báo thất bại; MUST NOT gửi dữ liệu Plan/chi phí hoặc email thử nghiệm/vô nghĩa. | Nghĩa vụ học phần; sự kiện, người nhận, nội dung và xử lý lỗi đã làm rõ |
| FR-015 | Hệ thống MUST từ chối truy cập không xác thực hoặc không đủ quyền bằng kết quả dễ hiểu và không làm lộ tài nguyên riêng tư. | Đã xác nhận theo Constitution |
| FR-016 | Hệ thống MUST xử lý dữ liệu trùng lặp, tài nguyên không tồn tại và lỗi lưu trữ mà không báo thành công giả. | Đã xác nhận theo Constitution |
| FR-017 | Hệ thống MUST yêu cầu xác nhận rõ trước khi xóa Plan; thao tác xóa MUST xóa Plan cùng toàn bộ khoản chi và phần chịu liên quan, không để lại dữ liệu chi phí mồ côi. | Đã làm rõ |
| FR-018 | Hệ thống MUST ghi nhận nghiên cứu website tương tự và liên kết kết luận nghiên cứu với quyết định sản phẩm có căn cứ. | Nghĩa vụ học phần |
| FR-019 | Hệ thống MUST cung cấp phân tích vai trò/chức năng, ERD, lập luận 3NF và dữ liệu mẫu phù hợp với yêu cầu đã duyệt. | Nghĩa vụ học phần |
| FR-020 | Hệ thống MUST có UML phù hợp với luồng thực tế và giao diện Figma truy vết đến yêu cầu; không tạo sơ đồ/màn hình trang trí. | Nghĩa vụ học phần |
| FR-021 | Hệ thống MUST có bằng chứng kiểm thử, triển khai, vận hành, tài liệu kỹ thuật và báo cáo phản ánh đúng trạng thái thật. | Nghĩa vụ học phần |
| FR-022 | Các hợp đồng trao đổi, trường dữ liệu và quy tắc chi tiết MUST được chốt sau khi xác nhận quy trình; đặc tả này không ấn định danh sách giao tiếp hoặc lược đồ vật lý. | Ràng buộc phạm vi |
| FR-023 | Mỗi khoản chi nhóm MUST có đúng một người thanh toán là thành viên hiện tại của Plan và một hoặc nhiều phần chịu được phân bổ theo từng khoản bằng chia đều hoặc số tiền tùy chỉnh nguyên VND; tổng các phần chịu MUST bằng số tiền khoản chi. Khi chia đều có phần dư VND, phân bổ phần dư theo thứ tự thành viên trong Plan. Hệ thống MUST tính số đã trả, phần chịu và chênh lệch; MUST NOT ghi nhận hoàn trả hoặc giao dịch tiền thật. | Đã làm rõ |
| FR-024 | Hệ thống MUST lưu khoản chi theo từng bản ghi; một địa điểm Plan MAY có nhiều khoản chi. Bảng thành viên × địa điểm MUST là tổng hợp tiền đã thanh toán theo người trả, không phải dữ liệu nguồn thay cho khoản chi. | Đã làm rõ |
| FR-025 | Hệ thống MUST hỗ trợ User đã đăng nhập đăng, sửa và xóa bài minigame của chính mình; bài mới MUST ở trạng thái chờ Admin duyệt và MUST NOT hiển thị với Guest trước khi được duyệt; Guest MUST được đọc bài đã công khai. Bài MUST chỉ chia sẻ thông tin và MUST NOT thực thi hoặc chơi minigame trực tuyến. | Core; quyền đăng/xem và duyệt trước khi công khai đã làm rõ |
| FR-026 | Bài minigame MUST có tên và luật/hướng dẫn chơi; MAY có ảnh minh họa, mô tả, số người phù hợp, thời lượng, dụng cụ và thể loại thuộc danh mục thống nhất; từ khóa tự do là tùy chọn; tác giả và thời gian đăng MUST được ghi nhận. | Core; trường bắt buộc và trạng thái ban đầu đã làm rõ; hành vi duyệt khi sửa bài đã công khai cần chốt trước triển khai |
| FR-027 | User MUST có thể tìm/lọc bài đã công khai theo số người chơi, thời lượng hoặc thể loại; thể loại MUST được chọn từ danh mục thống nhất và MAY có từ khóa tự do. User đã đăng nhập MUST có thể thích, bình luận, lưu yêu thích và báo cáo bài đã công khai; Admin MUST có thể duyệt bài chờ, xem báo cáo và giữ/ẩn/xóa bài vi phạm; hệ thống MUST gợi ý bài phù hợp số thành viên Plan mà không tự thêm bài hoặc tạo phiên chơi. | Core; nhóm chức năng và quy trình duyệt trước khi công khai đã xác nhận |
| FR-028 | Website MUST NOT cung cấp phòng minigame, chơi trực tuyến, xử lý lượt chơi hoặc giao dịch/hoàn trả tiền thật cho tính năng chia chi phí. | Out of Scope |
| FR-029 | Hệ thống MUST từ chối xóa/rời thành viên Plan hoặc xóa địa điểm khi còn khoản chi tham chiếu; chủ Plan MUST xử lý các khoản chi liên quan trước. Hệ thống MUST NOT xóa khoản chi dây chuyền. | Đã làm rõ |
| FR-030 | Admin MUST có thể tạo, xem, sửa và xóa mọi Plan và khoản chi; Plan do Admin tạo MUST thuộc sở hữu của Admin tạo Plan; mọi thao tác MUST tuân thủ các quy tắc toàn vẹn và xác nhận xóa đã nêu. Quyền này MUST NOT tự mở rộng sang quản lý tài khoản User hoặc nội dung khác. | Đã làm rõ |
| FR-031 | Hệ thống MUST ghi nhật ký khi Admin tạo/sửa/xóa Plan hoặc khoản chi, gồm Admin thực hiện, thời điểm, loại thao tác và bản ghi bị tác động; MUST NOT tạo nhật ký thao tác chỉ vì Admin xem dữ liệu. Nhật ký MUST NOT lưu nội dung nhạy cảm không cần thiết. | Đã làm rõ |

### 5.2. Quy tắc nghiệp vụ và kiểm tra dữ liệu

- Ngày kết thúc chuyến đi không được sớm hơn ngày bắt đầu; ngày bắt đầu/kết thúc
  có thể bằng nhau hay không cần nhóm xác nhận.
- Mỗi Plan có đúng một điểm đến; hoạt động có tên, giờ bắt đầu/kết thúc, địa
  điểm, hướng dẫn và ghi chú, được nhóm theo ngày. Mặc định sắp xếp theo giờ
  bắt đầu, giờ trùng giữ thứ tự thêm; User có thể chuyển sang sắp xếp thủ công
  và giữ thứ tự đó đến khi chọn lại sắp xếp theo giờ.
- Số tiền khoản chi phải dương và dùng VND; phần chịu tùy chỉnh phải là số
  nguyên VND. Hệ thống không hỗ trợ tiền tệ khác hoặc khoản chi hoàn/âm.
- Tổng chi là tổng của các khoản được xác định là còn hiệu lực; không được cộng
  lặp khi cập nhật, tải lại hoặc hiển thị nhiều trang kết quả.
- Không cho thành viên rời Plan hoặc xóa địa điểm khi còn khoản chi liên quan;
  trước hết phải xử lý các khoản chi đó. Hệ thống không tự xóa dây chuyền dữ
  liệu khoản chi.
- Khi chủ Plan xác nhận xóa Plan, hệ thống xóa Plan cùng toàn bộ khoản chi và
  phần chịu liên quan; không giữ lại các khoản chi tách khỏi Plan.
- User không được truy cập dữ liệu riêng của người khác chỉ bằng cách thay đổi
  định danh tài nguyên.
- Xóa Plan có khoản chi phải có xác nhận rõ và xóa toàn bộ khoản chi/phần chịu
  liên quan trong cùng thao tác; không được báo xóa thành công nếu dữ liệu bị
  xóa một phần.
- Mỗi yêu cầu tìm kiếm/tổng hợp phải áp dụng cùng ranh giới truy cập như xem dữ
  liệu nguồn.
- Trạng thái thành công chỉ được hiển thị khi thao tác hoàn tất; lỗi lưu trữ,
  email hoặc mạng không được tạo kết quả thành công giả.

### 5.3. Yêu cầu phi chức năng

| Nhóm | Yêu cầu đo được hoặc kiểm chứng được |
|---|---|
| Bảo mật | 100% kiểm thử truy cập âm tính đã xác định phải xác nhận Guest/User không đọc hoặc sửa tài nguyên ngoài quyền; thông báo không chứa thông tin nội bộ nhạy cảm. |
| Riêng tư | Màn hình, tìm kiếm, thống kê, bản in và email chỉ chứa dữ liệu người nhận được phép xem. |
| Hiệu năng | Trên môi trường demo thống nhất với tối đa 100 kế hoạch và 1.000 khoản chi mẫu, ít nhất 95% thao tác tìm kiếm/tải danh sách hoàn tất và hiển thị trong 3 giây. Cần đo lại và điều chỉnh nếu môi trường học phần khác biệt đáng kể. |
| Tính dễ dùng | Ít nhất 90% người tham gia kiểm thử đại diện hoàn thành tạo kế hoạch và ghi khoản chi mẫu trong lần đầu mà không cần người hướng dẫn; thời gian mục tiêu cho cả hai thao tác không quá 3 phút. |
| Khả năng tiếp cận | Các thao tác chính dùng được bằng bàn phím; trường nhập có nhãn; trạng thái focus và lỗi nhìn thấy được; không dùng màu sắc làm tín hiệu duy nhất. |
| Responsive | Các luồng cốt lõi phải được kiểm tra tại viewport rộng 375 px, 768 px và 1280 px; không mất nội dung hoặc thao tác chính do tràn ngang không chủ đích. |
| Tương thích | Kiểm tra trên hai phiên bản ổn định gần nhất của các trình duyệt desktop hiện đại được nhóm thống nhất trong kế hoạch kiểm thử. |
| Toàn vẹn dữ liệu | Kiểm thử chứng minh cập nhật/xóa phản ánh đúng danh sách và số liệu; lỗi giữa chừng không để lại kết quả một phần không nhất quán. |
| Bảo trì | Yêu cầu, giao diện, dữ liệu, hợp đồng trao đổi và test case phải truy vết được; thay đổi hành vi có kiểm thử hồi quy phù hợp. |
| Audit thao tác đặc quyền | 100% thao tác tạo/sửa/xóa Plan hoặc khoản chi của Admin phải có bản ghi gồm người thực hiện, thời điểm, loại thao tác và bản ghi bị tác động; thao tác xem không tạo bản ghi audit. |
| Vận hành | Tài liệu triển khai phải nêu cấu hình theo môi trường, bảo vệ secret, kiểm tra sức khỏe, xác minh kết nối lưu trữ và hướng khôi phục/rollback. |

Các ngưỡng trên là tiêu chí đề xuất cho đồ án, không phải số liệu hiện trạng.
Nhóm có thể điều chỉnh ngưỡng trước khi chốt kế hoạch kiểm thử nếu ghi lại
lý do và cách đo.

### 5.4. Xử lý lỗi và trường hợp biên

| Trường hợp | Hành vi cần đạt |
|---|---|
| Thiếu trường bắt buộc/sai định dạng | Từ chối thao tác; nêu trường cần sửa; giữ lại dữ liệu nhập hợp lệ khi phù hợp. |
| Ngày kết thúc trước ngày bắt đầu | Không lưu; thông báo quy tắc ngày cụ thể. |
| Khoản chi thiếu, bằng 0, âm hoặc không hợp lệ | Không lưu; không thay đổi tổng. |
| Tên loại chi chỉ khác chữ hoa/thường hoặc khoảng trắng đầu/cuối với nhãn đã có | Yêu cầu User chọn nhãn hiện có; không lưu biến thể như một nhãn mới. |
| Tổng phần chịu khác số tiền khoản chi | Từ chối lưu; yêu cầu chỉnh sửa phân bổ. Khi chia đều, phân bổ phần dư VND theo thứ tự thành viên trong Plan để tổng khớp tuyệt đối. |
| Thành viên muốn rời Plan hoặc địa điểm muốn bị xóa khi còn khoản chi liên quan | Từ chối thao tác; yêu cầu chủ Plan xử lý các khoản chi liên quan trước; không xóa dây chuyền. |
| Chủ Plan xác nhận xóa Plan có khoản chi | Xóa Plan cùng khoản chi và phần chịu liên quan; thao tác phải nhất quán, không để lại dữ liệu mồ côi hoặc trạng thái xóa thành công một phần. |
| Khoản chi có thể trùng | Nếu khoản mới cùng Plan, ngày, số tiền, người thanh toán và địa điểm với khoản đã có, cảnh báo người dùng; vẫn cho lưu sau khi người dùng xác nhận. |
| Dữ liệu trùng khác | Áp dụng quy tắc uniqueness được duyệt; báo rõ xung đột, không ghi đè âm thầm. |
| Chưa xác thực | Từ chối thao tác riêng tư và yêu cầu xác thực mà không tiết lộ dữ liệu. |
| Không có quyền sở hữu/quyền chia sẻ | Từ chối xem/sửa/xóa; không tiết lộ sự tồn tại hoặc nội dung nhạy cảm quá mức. |
| Tài nguyên không tồn tại | Trả trạng thái không tìm thấy phù hợp; không hiển thị dữ liệu cũ như còn tồn tại. |
| Kết quả tìm kiếm rỗng | Hiển thị trạng thái rỗng, không phải lỗi hệ thống. |
| Mất mạng hoặc lỗi lưu trữ | Thông báo thao tác chưa hoàn tất; cho phép thử lại an toàn; tránh gửi trùng khi người dùng thử lại. |
| Dịch vụ tìm kiếm điểm đến bên ngoài không khả dụng | Cho phép User nhập điểm đến thủ công; Plan đã lưu vẫn hiển thị theo thông tin điểm đến đã lưu. |
| Xung đột cập nhật đồng thời | Không ghi đè âm thầm thay đổi mới hơn; báo người dùng tải lại hoặc xác nhận giải pháp được duyệt. |
| Lỗi tạm thời gửi email | Tự thử lại tối đa 3 lần; nếu vẫn lỗi, báo thất bại rõ ràng và không báo gửi thành công. |
| Yêu cầu in | Không bao gồm nội dung ngoài quyền; nội dung tổng hợp phải khớp dữ liệu được hiển thị. |

## 6. Đánh giá ERD và dữ liệu

### 6.1. Kiểm kê và đánh giá hiện trạng

Không tìm thấy ERD, sơ đồ quan hệ, tài liệu entity hoặc migration nghiệp vụ
trong các file repository đã kiểm tra. Script khởi tạo có `test_connection` dùng
để xác nhận kết nối; đây không phải entity nghiệp vụ. Vì vậy không thể đánh giá
cardinality, khóa, thuộc tính, ràng buộc hoặc chuẩn hóa của một ERD hiện có.
Không được mô tả thiết kế này là ERD hiện tại của sản phẩm.

### 6.2. Các khái niệm dữ liệu cần khảo sát

Bảng dưới đây chỉ là danh sách khái niệm để phân tích sau khi nghiệp vụ được
duyệt, không phải danh sách bảng đã quyết định.

| Khái niệm | Mục đích nghiệp vụ tiềm năng | Liên quan yêu cầu | Trạng thái |
|---|---|---|---|
| Tài khoản/định danh | Xác định User và quyền sở hữu dữ liệu. | FR-002, FR-003, FR-015 | Nền tảng xác thực đã cấu hình; cách liên kết dữ liệu nghiệp vụ cần thiết kế. |
| Nhật ký thao tác Admin | Truy vết thao tác tạo/sửa/xóa có đặc quyền đối với Plan và khoản chi. | FR-031 | Gồm Admin, thời điểm, loại thao tác và bản ghi bị tác động; không ghi lần xem hoặc nội dung nhạy cảm không cần thiết. Cách lưu trữ, thời hạn lưu và quyền đọc nhật ký cần thiết kế. |
| Kế hoạch du lịch | Gom thông tin một chuyến đi của User. | PRD-001, PROP-001, FR-005, FR-007, FR-017 | Plan nhóm chỉ được chủ Plan và thành viên được thêm trực tiếp xem; không chia sẻ công khai. Khi chủ Plan xác nhận xóa, Plan và khoản chi/phần chịu liên quan bị xóa cùng nhau. |
| Khoản chi | Bản ghi chi phí riêng, có người thanh toán, địa điểm tùy chọn, số tiền, thời gian, ghi chú; có thể có nhiều người chịu phần chi. | PRD-002, PROP-002, FR-007–009 | Thuộc một Plan; một người thanh toán; phần chịu liên kết nhiều thành viên; có thể liên kết một địa điểm. Nhiều khoản có thể thuộc cùng địa điểm. |
| Điểm đến | Lựa chọn từ dịch vụ tìm kiếm bên ngoài hoặc tên nhập thủ công khi dịch vụ không khả dụng. | PROP-001, FR-006 | Lưu mã nhà cung cấp và tên hiển thị cùng địa chỉ/tọa độ nếu kết quả cung cấp; tên thủ công không có mã nhà cung cấp. Nhà cung cấp cụ thể chưa được chọn. |
| Thành viên Plan | Biểu diễn chủ Plan và các User đã đăng ký được chủ Plan thêm trực tiếp; thành viên chỉ có quyền xem khoản chi. | FR-007–009, FR-029, FR-030 | Chỉ chủ Plan và thành viên được thêm xem Plan/chi phí; Admin có CRUD mọi Plan/khoản chi; không chia sẻ công khai. Không thể rời/xóa thành viên còn được khoản chi tham chiếu; xử lý khoản chi trước. |
| Phần chịu khoản chi | Số tiền mà một thành viên chịu trong một bản ghi chi phí. | Chia đều/tùy chỉnh, tính tổng và chênh lệch. | Mỗi dòng gắn một khoản chi và một thành viên; tổng phân bổ phải khớp chính xác số tiền khoản chi bằng VND. |
| Địa điểm Plan | Nhóm khoản chi theo địa điểm để tạo bảng tổng hợp. | Tổng hợp thành viên × địa điểm. | Một địa điểm có thể có nhiều khoản chi; không thể xóa khi còn khoản chi tham chiếu và không xóa dữ liệu dây chuyền. |
| Mục lịch trình | Hoạt động trong chuyến đi, được nhóm theo ngày của Plan. | PROP-005, FR-006 | Có tên, giờ bắt đầu/kết thúc, địa điểm, hướng dẫn và ghi chú; mặc định theo giờ bắt đầu, giờ trùng giữ thứ tự thêm; User có thể chọn chế độ sắp xếp thủ công. |
| Bài đăng minigame | Nội dung cộng đồng chia sẻ minigame có thể chơi ngoài đời. | User đăng/sửa/xóa bài của mình; Guest đọc bài đã được Admin duyệt. | PROP-006, FR-025–028 | Có tác giả và thời điểm đăng; tên cùng luật/hướng dẫn chơi bắt buộc; ảnh, mô tả, thuộc tính minigame khác tùy chọn; bài mới chờ Admin duyệt; Admin xem báo cáo và giữ/ẩn/xóa bài vi phạm. Cách xử lý sửa bài đã công khai cần chốt trước triển khai. Không phải game session. |
| Tương tác bài đăng | Like, comment, yêu thích và báo cáo. | Tương tác, lưu hoặc báo nội dung cộng đồng. | FR-027 | Core; trạng thái bài và báo cáo ảnh hưởng khả năng hiển thị; quyền sửa/xóa bình luận và quy tắc kiểm duyệt chi tiết cần thiết kế. |

### 6.3. Định hướng ERD và 3NF

- Chỉ chốt entity/attribute sau khi luồng nghiệp vụ, quyền sở hữu, vòng đời và
  yêu cầu báo cáo được duyệt.
- ERD cần thể hiện PK/FK, cardinality, optionality, unique/not-null/check phù
  hợp, cùng quy tắc xóa và toàn vẹn tham chiếu.
- Thiết kế quan hệ hướng 3NF; mỗi thuộc tính không khóa phải phụ thuộc vào khóa,
  toàn bộ khóa và không phụ thuộc bắc cầu vào thuộc tính không khóa khác.
- Không lặp thông tin người dùng, chuyến đi, khoản chi hoặc danh mục chỉ để
  thuận tiện hiển thị; ngoại lệ phi chuẩn hóa cần đo lợi ích và được duyệt.
- Dữ liệu mẫu phải là dữ liệu giả lập, có thể kiểm thử quyền sở hữu và các
  trường hợp cá nhân/nhóm, không đưa dữ liệu cá nhân thật vào repository.
- Sau khi duyệt, deliverable ERD phải mô tả mục đích, thuộc tính quan trọng,
  PK/FK, quan hệ/cardinality, ràng buộc, FR liên quan và lập luận 3NF cho từng
  entity.
- Không tạo hoặc chạy migration trong giai đoạn đặc tả.

### 6.4. ERD-to-FR Mapping (khái niệm, chưa phải schema)

| Khái niệm | Mục đích nghiệp vụ | Workflow | Yêu cầu liên quan | Quan hệ dự kiến |
|---|---|---|---|---|
| Tài khoản/định danh | Quyền sở hữu và xác thực. | Truy cập, quản lý dữ liệu cá nhân. | FR-002, FR-003, FR-015 | User sở hữu kế hoạch; cách liên kết phụ thuộc thiết kế xác thực. |
| Kế hoạch du lịch | Tập hợp thông tin chuyến đi tại đúng một điểm đến, lịch trình theo ngày và khu vực chi phí. | Tạo/xem/sửa/xóa, quản lý lịch trình, tìm kiếm, in, tổng hợp chi. | FR-005–010, FR-012, FR-017 | Có thể liên kết nhiều hoạt động lịch trình, khoản chi và thành viên Plan; chỉ chủ Plan/thành viên được thêm xem; xác nhận xóa Plan sẽ xóa khoản chi và phần chịu liên quan. |
| Mục lịch trình | Hoạt động được gắn với một ngày trong lịch trình Plan. | Thêm/xem/chỉnh sửa hoạt động theo ngày. | PROP-005, FR-006 | Thuộc một Plan và được nhóm theo ngày; có tên, giờ bắt đầu/kết thúc, địa điểm, hướng dẫn và ghi chú; mặc định sắp xếp theo giờ bắt đầu, giờ trùng giữ thứ tự thêm; User có thể chọn chế độ sắp xếp thủ công. |
| Khoản chi | Bản ghi chi phí riêng, có một người thanh toán và tên loại chi tùy chọn do User nhập tự do. | Thêm/sửa/xóa, tính tổng theo Plan, thành viên, địa điểm và loại chi. | FR-007–009, FR-012–014 | Thuộc Plan; một người thanh toán; có thể có nhiều dòng phần chịu; nhiều khoản có thể thuộc cùng địa điểm; loại chi dùng để tổng hợp, thiếu nhãn thuộc nhóm “Chưa phân loại”. |
| Thành viên Plan | Tài khoản User đã đăng ký được chủ Plan thêm trực tiếp vào Plan. | Xem Plan và bảng tổng hợp chi phí. | FR-007–009, FR-029 | Chỉ chủ Plan và thành viên được thêm được xem; thành viên chỉ đọc khoản chi. Không thể rời/xóa thành viên còn được khoản chi tham chiếu; phải xử lý khoản chi trước. |
| Phần chịu khoản chi | Phần số tiền phân bổ cho một thành viên. | Tính phần chịu và chênh lệch. | FR-008, FR-009 | Thuộc một khoản chi và một thành viên Plan; tổng phần chịu khớp chính xác tổng khoản chi VND. |
| Địa điểm Plan | Vị trí để nhóm khoản chi và hiển thị cột tổng hợp. | Nhập chi phí theo nơi; tổng hợp thành viên × địa điểm. | FR-008, FR-009, FR-029 | Một địa điểm có nhiều khoản chi; không thể xóa khi còn khoản chi tham chiếu, phải xử lý khoản chi trước. |
| Bài đăng minigame | Bài viết giới thiệu một minigame ngoài đời. | User tạo/quản lý bài; Guest đọc bài công khai; tìm/lọc và gợi ý theo Plan. | FR-025–028 | Có tác giả; thể loại từ danh mục thống nhất và từ khóa tự do tùy chọn; ảnh và trường mô tả ứng viên; có thể liên kết like, bình luận, yêu thích, báo cáo. Không biểu diễn phòng chơi hoặc phiên chơi. |
| Tương tác minigame | Lượt thích, bình luận, yêu thích, báo cáo và trạng thái kiểm duyệt. | Tương tác cộng đồng và xử lý nội dung vi phạm. | FR-027 | Quan hệ tới bài đăng và tài khoản; tính duy nhất lượt thích/yêu thích; quyền thao tác và vòng đời bình luận cần làm rõ trong thiết kế. |
| Điểm đến | User tìm kiếm/chọn qua dịch vụ bên ngoài hoặc nhập thủ công khi dịch vụ không khả dụng. | Ghi kế hoạch, tìm kiếm. | PROP-001, FR-006, PROP-003 | Mỗi Plan có đúng một điểm đến; lựa chọn từ dịch vụ lưu mã nhà cung cấp và tên hiển thị, địa chỉ/tọa độ nếu có; nhập thủ công không có mã nhà cung cấp. |
| Thành viên/lịch trình | Chủ Plan thêm trực tiếp User đã đăng ký; lịch trình có hoạt động theo ngày. | Quản lý thành viên Plan và hoạt động lịch trình. | FR-006, FR-007 | Quy tắc quyền và quan hệ nghiệp vụ đã được xác nhận; cardinality/khóa vật lý thuộc ERD chưa thiết kế. |

### 6.5. Workflow-to-Data Mapping

| Workflow | Actor | Dữ liệu cần | Khái niệm liên quan | Kiểm tra | Kết quả mong muốn |
|---|---|---|---|---|---|
| Tạo và cập nhật kế hoạch | User | Chủ sở hữu, tên, điểm đến được chọn/nhập, ngày, mô tả tùy chọn | Tài khoản, kế hoạch, dịch vụ tìm kiếm điểm đến | Quyền sở hữu; nguồn điểm đến hợp lệ; trường bắt buộc; thứ tự ngày | User thấy phiên bản mới nhất của kế hoạch mình. |
| Ghi khoản chi Plan nhóm | Chủ Plan | Người thanh toán, địa điểm, số tiền, thời gian, ghi chú, cách chia và phần chịu | Plan, thành viên, địa điểm Plan, khoản chi, phần chịu | Chỉ chủ Plan được thay đổi; người thanh toán và người nhận phần chịu thuộc Plan; tổng phần chịu khớp khoản chi | Có bản ghi chi riêng; các thành viên khác chỉ đọc được. |
| Tìm kiếm/tổng hợp/in | User/thành viên Plan theo quyền được duyệt | Tiêu chí tìm, khoản chi hiệu lực, tên loại chi tự do, người thanh toán, phần chịu, địa điểm | Plan, thành viên Plan, địa điểm, khoản chi, phần chịu | Phạm vi quyền; tiêu chí; VND, phần chịu tùy chỉnh nguyên VND và so khớp tên loại chi | Tổng theo Plan, thành viên, địa điểm và loại chi nhất quán; ma trận thành viên × địa điểm là dữ liệu tổng hợp. |
| Quản trị Plan và chi phí | Admin | Mọi Plan, khoản chi và phần chịu liên quan | Tạo/xem/sửa/xóa mọi Plan/khoản chi; kiểm duyệt minigame theo FR-027 | Kiểm tra quyền Admin; áp dụng xác nhận xóa và toàn vẹn dữ liệu; ghi nhật ký tạo/sửa/xóa; không quản lý tài khoản User hoặc nội dung khác | FR-017, FR-027, FR-030/031 |
| Đăng và khám phá minigame | User đăng bài của mình; Guest đọc bài được duyệt | Tên và luật/hướng dẫn bắt buộc; mô tả, ảnh, số người, thời lượng, dụng cụ, thể loại và từ khóa tùy chọn; tác giả, thời điểm | Bài đăng và tương tác minigame | Chỉ tác giả sửa/xóa bài của mình; bài mới chờ Admin duyệt; tìm/lọc nội dung đã công khai; thể loại từ danh mục thống nhất, có thể thêm từ khóa tự do; like/bình luận/yêu thích/báo cáo; Admin duyệt/ẩn/xóa; gợi ý theo số thành viên | Nội dung công khai có thể khám phá sau khi duyệt; không khởi tạo trò chơi trực tuyến. |

## 7. UI/UX và kiểm tra thiết kế

### 7.1. Kiểm kê màn hình hiện tại

Repository hiện chỉ có màn hình kiểm tra trạng thái kết nối dịch vụ, với tiêu đề,
trạng thái tải/lỗi và trạng thái kết quả. Không tìm thấy màn hình chức năng du
lịch, màn hình quản trị, file/link Figma hoặc bộ quy tắc design system. Do đó
không có căn cứ để khẳng định có sidebar, header nghiệp vụ hay thứ tự section
đã được duyệt.

### 7.2. Chính sách bảo toàn layout và cải thiện

- Khi nhóm cung cấp bản Figma/screenshot nghiệp vụ có thể truy cập, layout lớn,
  vị trí tương đối của sidebar/header/khu nội dung và thứ tự section phải được
  giữ khi tinh chỉnh visual.
- Không được tuyên bố đã bảo toàn một layout nghiệp vụ chưa tìm thấy.
- Điều chỉnh màu, typography, spacing, hierarchy, form, button, trạng thái
  hover/focus/disabled/loading/empty/error theo một design system thống nhất.
- Responsive được phép thay đổi kích thước, xếp chồng, ẩn/hiện điều khiển phụ
  để giữ luồng chính trên desktop/tablet/mobile; không tự ý tái cấu trúc
  information architecture desktop.
- Nếu cần đổi vị trí thành phần lớn để sửa lỗi usability/accessibility, ghi
  lý do, tác động và xin duyệt riêng.
- Không tạo màn hình, biểu đồ, thẻ hoặc animation chỉ để tăng độ phức tạp.

### 7.3. UI-to-Functional-Requirements Mapping

Màn hình dưới đây là đề xuất để truy vết yêu cầu, không khẳng định đã được
thiết kế hoặc triển khai.

| Màn hình | Actor | Thành phần chính | Thao tác | Hành vi mong đợi | Yêu cầu |
|---|---|---|---|---|---|
| Trang công khai | Guest | Mô tả sản phẩm, điều hướng xác thực | Xem, đăng ký, đăng nhập | Nêu đúng khả năng đã xác nhận; không để lộ dữ liệu riêng tư | FR-004, User Story 5 |
| Đăng ký/đăng nhập | Guest/User | Form và phản hồi lỗi | Gửi thông tin xác thực | Cho phép tiếp tục khi xác thực hợp lệ; giải thích lỗi phù hợp | User Story 1 |
| Danh sách kế hoạch | User | Danh sách, tìm kiếm, trạng thái rỗng | Tạo, tìm, mở, xóa | Chỉ hiển thị dữ liệu có quyền | FR-005, FR-010, FR-011 |
| Chi tiết/chỉnh sửa kế hoạch | User | Thông tin Plan, một điểm đến, lịch trình nhóm theo ngày và form | Xem/sửa Plan; thêm/sửa/xóa hoạt động; xác nhận xóa | Kiểm tra dữ liệu và giữ nội dung nhập hợp lệ khi có lỗi | FR-005, FR-006, FR-017 |
| Khoản chi và tổng hợp | Chủ Plan; thành viên khác chỉ xem | Bản ghi khoản chi, người trả, phần chịu, bảng thành viên × địa điểm và tổng | Chủ Plan thêm/sửa/xóa; thành viên xem tổng theo Plan/thành viên/địa điểm | Ô ma trận tổng hợp tiền thành viên đã thanh toán tại địa điểm; tổng phần chịu và chênh lệch riêng; không tạo thanh toán; chặn xóa thành viên/địa điểm còn khoản chi liên quan | FR-007–009, FR-013, FR-023/024/029 |
| Bản in | User | Tóm tắt kế hoạch/chi phí | Xem trước, in | Chỉ in dữ liệu hợp lệ, đúng quyền và đọc được | FR-012 |
| Quản lý Plan/chi phí | Admin | Danh sách và chi tiết mọi Plan, khoản chi | Tạo, xem, sửa, xóa | Chỉ Admin được dùng chức năng; thao tác xóa có xác nhận và tuân thủ quy tắc dữ liệu liên quan; ghi audit cho tạo/sửa/xóa, không ghi lần xem | FR-017, FR-030, FR-031 |
| Kiểm duyệt minigame | Admin | Báo cáo và bài bị báo cáo | Xem báo cáo, giữ nguyên hoặc ẩn/xóa bài vi phạm | Ẩn bài khỏi Guest; không suy ra quyền quản trị khác | FR-027 |
| Cộng đồng minigame | User, Guest và Admin kiểm duyệt | Danh sách/bài viết, tìm/lọc, bình luận, yêu thích, like, báo cáo, hàng đợi kiểm duyệt | Guest đọc bài công khai; User tạo/sửa/xóa bài của mình và tương tác; Admin xử lý báo cáo | Bài vi phạm có thể bị ẩn/xóa; gợi ý theo số thành viên Plan; không có lượt chơi | FR-025–028 |

### 7.4. Design Gap Analysis

| Vấn đề | Artifact | Hiện trạng | Cải thiện đề xuất | Lý do | Ưu tiên |
|---|---|---|---|---|---|
| Không có ERD nghiệp vụ trong repository | ERD/database | Chỉ có bảng kiểm tra kết nối | Thu thập ERD hiện có nếu lưu ngoài repo; đối chiếu với FR; sau đó duyệt mô hình | Tránh khẳng định sai schema và tránh tạo bảng theo giả định | P1 |
| Không có Figma hoặc màn hình nghiệp vụ để kiểm tra | UI/UX | Chỉ có màn hình trạng thái hệ thống | Xác định nguồn Figma; kiểm kê trang/section và gắn với FR trước khi tinh chỉnh | Chưa thể giữ layout nếu layout chưa có trong artifacts được cung cấp | P1 |
| Chưa có luồng nghiệp vụ sản phẩm trong ứng dụng | Product UI | UI hiện tại chỉ kiểm tra sức khỏe dịch vụ | Duyệt luồng User Story 1–4 rồi mới thiết kế màn hình | Đảm bảo giao diện giải quyết nhu cầu thay vì thêm trang tùy ý | P1 |
| Chi tiết vận hành nhật ký Admin | Role model/audit | Sự kiện và trường cần ghi đã được xác nhận; nơi lưu, thời hạn lưu và quyền đọc nhật ký chưa quyết định | Thiết kế lưu trữ nhật ký không lộ dữ liệu nhạy cảm; xác định retention và quyền truy cập khi lập plan kỹ thuật | Bảo vệ và truy xuất nhật ký đặc quyền | P2 |
| ERD/UI nghiệp vụ nhóm chưa có trong repository | Expense/group flow | Quyền thêm thành viên trực tiếp, quyền khoản chi, phân bổ và quy tắc chặn xóa khi còn khoản chi đã được làm rõ; chưa có ERD hoặc UI nghiệp vụ | Khi lập thiết kế, thể hiện đúng quyền chủ Plan/thành viên, ranh giới riêng tư, phần chịu và ràng buộc vòng đời | Tác động đến quyền, ERD và UI | P1 |

## 8. Bảng đối chiếu giữa workflow, UI và dữ liệu

### 8.1. Điểm chưa khớp cần giải quyết

- Tìm kiếm, thống kê, in và email là yêu cầu học phần nhưng hiện chưa có
  workflow sản phẩm/endpoint nghiệp vụ tương ứng trong repository.
- Cách phân bổ chi phí đã làm rõ ở mức từng khoản (một người thanh toán; chia
  đều hoặc số tiền tùy chỉnh; tính chênh lệch; không ghi nhận hoàn trả). Chủ
  Plan thêm User đã đăng ký trực tiếp; chỉ chủ Plan và thành viên được thêm xem
  Plan/chi phí; thành viên chỉ đọc khoản chi và không có chia sẻ công khai.
- Bảng thành viên × địa điểm là tổng hợp, không phải nguồn dữ liệu khoản chi;
  một địa điểm có thể có nhiều bản ghi chi riêng.
- Màn hình tạo/sửa/xóa kế hoạch và khoản chi chưa có thiết kế hoặc triển khai
  trong repository.
- Điểm đến được tìm qua dịch vụ bên ngoài; lưu mã nhà cung cấp, tên hiển thị và
  địa chỉ/tọa độ nếu kết quả cung cấp. Khi dịch vụ không khả dụng, User được
  nhập tên điểm đến thủ công; nhà cung cấp cụ thể chưa được chọn.
- Một điểm đến, lịch trình theo ngày, các trường hoạt động và quy tắc sắp xếp
  mặc định/thủ công đã được xác nhận; nhà cung cấp điểm đến vẫn chưa được chọn.
- Quyền Admin quản lý Plan/chi phí, kiểm duyệt minigame và các trường audit đã
  được xác nhận; chưa có UI nghiệp vụ trong repository. Nơi lưu, thời hạn lưu
  và quyền đọc nhật ký thuộc quyết định thiết kế vận hành.
- Chưa có màn hình, entity hoặc luồng cộng đồng minigame trong repository.
  Quyền đăng/sửa/xóa của tác giả, đọc bài công khai, các chức năng cộng đồng
  phiên bản đầu và quyền kiểm duyệt Admin đã được xác nhận; trường bắt buộc và
  quy tắc kiểm duyệt chi tiết vẫn cần xác định.
- Không có cơ sở để kiểm tra chéo một ERD/UI hiện có; cần nhóm cung cấp tài liệu
  ngoài repository nếu chúng tồn tại.

### 8.2. Quy tắc thay đổi thiết kế

Mọi thay đổi ERD/UI được phân loại thành:

1. **Sửa lỗi bắt buộc**: cần để đáp ứng yêu cầu đã duyệt hoặc khắc phục lỗi.
2. **Cải thiện khuyến nghị**: cải thiện usability, toàn vẹn dữ liệu, bảo trì
   hoặc tính nhất quán; cần nêu lợi ích.
3. **Nâng cao tùy chọn**: thêm giá trị nhưng không bắt buộc.
4. **Quyết định chưa rõ**: cần nhóm xác nhận trước khi chốt.

Mỗi thay đổi lớn phải nêu thiết kế hiện tại (nếu có), đề xuất, lý do, tác động
nghiệp vụ/UI/dữ liệu, tương thích, ưu tiên và người phê duyệt. Đây là quy trình
review, không phải yêu cầu sửa Figma, source code hoặc database trong giai đoạn
đặc tả.

## 9. Kiểm thử và nghiệm thu

- Functional test cho từng kịch bản/tiêu chí chấp nhận đã duyệt.
- Kiểm thử hợp đồng trao đổi cho các luồng thay đổi; kiểm tra định dạng phản
  hồi, trạng thái kết quả và lỗi theo quy ước dự án.
- Kiểm thử thao tác giao diện: nhập liệu, trạng thái tải/rỗng/thành công/lỗi,
  thao tác bàn phím, responsive và xác nhận xóa.
- Kiểm thử validation cho dữ liệu thiếu/sai, ngày, số tiền, trùng lặp và xung
  đột cập nhật liên quan.
- Kiểm thử xác thực: chưa đăng nhập, thông tin không hợp lệ, đăng nhập hợp lệ,
  logout/token lifecycle sau khi chính sách được quyết định.
- Kiểm thử phân quyền: Guest, User sở hữu tài nguyên, User không sở hữu, Admin
  với từng quyền đã duyệt.
- Kiểm thử toàn vẹn dữ liệu: liên kết chủ sở hữu, kế hoạch/khoản chi, xóa/cập
  nhật và tính tổng.
- Kiểm thử tích hợp cho luồng UI–API–dữ liệu và lỗi mạng/lưu trữ/email có liên
  quan.
- Kiểm tra deployment trên môi trường được phê duyệt, health check, kết nối dữ
  liệu, cấu hình secret, khôi phục và rollback.
- Lưu test case, kết quả, môi trường và bằng chứng phù hợp để đưa vào báo cáo;
  không báo pass nếu chưa chạy.

**Definition of Done cho mỗi yêu cầu**: tiêu chí được duyệt; giao diện và
workflow liên quan hoàn tất; kiểm tra quyền/validation/error hoàn tất; test
liên quan chạy đạt; tài liệu/traceability cập nhật; review tuân thủ
Constitution; trạng thái triển khai trong báo cáo phản ánh đúng bằng chứng.

## 10. Ma trận truy vết yêu cầu học phần

Trạng thái phản ánh bằng chứng hiện có trong repository tại thời điểm tạo đặc
tả, không phải đánh giá năng lực cá nhân hoặc kết luận cuối kỳ.

| Yêu cầu học phần | Yêu cầu sản phẩm/chức năng | Tham chiếu đặc tả | Tiêu chí chấp nhận | Bằng chứng cần có | Trạng thái hiện tại |
|---|---|---|---|---|---|
| Khảo sát website tương tự | FR-018 | §3.1, §5.1, §12 | Nguồn thật, phân tích tính năng/nhu cầu, có kết luận riêng và không sao chép | Bảng khảo sát có URL, ngày truy cập, nguồn và phân tích | Not started |
| Phân tích vai trò/chức năng | FR-002, FR-003 | §2, §4, §12 | Mỗi vai trò có mục đích, giới hạn và thao tác đã duyệt | Use-case/role matrix được nhóm phê duyệt | Specified |
| ERD, khóa, quan hệ, toàn vẹn, dữ liệu mẫu | FR-019 | §6, §12 | ERD bao phủ workflow được duyệt; ràng buộc và dữ liệu mẫu được kiểm tra | ERD, data dictionary, migration sau giai đoạn thiết kế, seed/test data | Not started |
| Chuẩn hóa hướng 3NF | FR-019 | §6.3 | Mỗi entity có lập luận phụ thuộc hàm/3NF; ngoại lệ có lý do | Tài liệu đánh giá 3NF | Specified |
| UML | FR-020 | §3.1, §5.1, §12 | Mỗi diagram giải thích một yêu cầu/hành vi cụ thể | Use Case Diagram/description; activity/sequence/class khi phù hợp | Not started |
| UI/UX/Figma | FR-020 | §7, §12 | Màn hình đã duyệt gắn với actor/FR; layout lớn được giữ khi cải thiện; responsive và trạng thái được kiểm tra | Link/file Figma, review và kết quả kiểm thử viewport | Not started |
| HTML/HTML5, CSS/CSS3 | FR-021 | §3.1, §7 | Giao diện chạy đúng, semantic và tương thích mục tiêu | Source, kiểm thử giao diện | Not started — có UI khung, chưa có bằng chứng hoàn thành yêu cầu |
| Bootstrap khi phù hợp với thiết kế được duyệt | FR-021 | §3.1, §7 | Nếu sử dụng, giao diện phải giữ nhất quán và đáp ứng thiết kế; không thêm framework chỉ vì checklist | Thiết kế được duyệt, source và kiểm thử giao diện | Not started — cần xác nhận tính phù hợp |
| JavaScript và AJAX/jQuery | FR-021 | §3.1 | Dùng jQuery AJAX hoặc Fetch API cho luồng bất đồng bộ có ý nghĩa đối với sản phẩm | Demo luồng bất đồng bộ và kiểm thử | Not started |
| PHP, MySQL, CodeIgniter, MVC, client-server | FR-021 | Constitution, §3.1 | Có luồng nghiệp vụ thực tế qua kiến trúc đã thống nhất | Source, sơ đồ kiến trúc, test luồng tích hợp | Specified — một phần cấu hình/scaffold; chưa có nghiệp vụ |
| Thêm/sửa/xóa và hiển thị dữ liệu | FR-005, FR-007 | §4, §5.1 | CRUD kế hoạch/khoản chi được duyệt, có validation và quyền | Demo, test và tài liệu chức năng | Not started |
| Tìm kiếm | FR-010 | §4, §5.1 | Kết quả đúng phạm vi quyền; xử lý rỗng | Test truy vấn và demo | Not started |
| Thống kê | FR-009, FR-013 | §4, §5.1 | Tổng Plan/thành viên và tổng theo loại chi khớp các khoản hợp lệ theo quy tắc đã duyệt | Test tính tổng và demo | Not started |
| In ấn | FR-012 | §4, §5.1 | Tóm tắt gồm thông tin Plan, lịch trình theo ngày và tổng hợp chi phí; khớp dữ liệu và quyền | Mẫu in và kiểm thử | Not started |
| Gửi email | FR-014 | §5.1, §12 | Ba sự kiện/người nhận, nội dung tối thiểu và tối đa 3 lần thử lại khi lỗi tạm thời | Test gửi thành công/thất bại và cấu hình vận hành | Not started |
| Kiểm thử | FR-021 | §9 | Test case yêu cầu trọng yếu chạy đạt, kết quả lưu lại | Báo cáo test, test tự động/thủ công | Not started — hiện chỉ có kiểm thử scaffold/health |
| Triển khai, vận hành và bảo trì server | FR-021 | §3.1, §12, §14 | Có deployment kiểm chứng, health check và rollback/recovery; có hướng dẫn vận hành/bảo trì | Hướng dẫn, cấu hình môi trường, kết quả triển khai và vận hành | Not started; AWS là hướng, chưa có topology |
| Tài liệu kỹ thuật và báo cáo | FR-021 | §1, §9–§10, §12 | Báo cáo có trace tới artifacts và nêu đúng trạng thái | Báo cáo, tài liệu kiến trúc, DB, UML, UI, test, deploy | Not started |

## 11. Tiêu chí thành công

| ID | Kết quả đo được | Cách xác minh |
|---|---|---|
| SC-001 | Ít nhất 90% người tham gia kiểm thử đại diện hoàn thành tạo một kế hoạch và ghi một khoản chi hợp lệ trong lần đầu, không cần người hướng dẫn. | Kiểm thử usability có kịch bản và ghi nhận số người hoàn tất. |
| SC-002 | Người tham gia hoàn tất hai thao tác chính trên trong tối đa 3 phút ở ít nhất 90% lượt thử thành công. | Ghi thời gian bắt đầu/kết thúc cho từng lượt kiểm thử. |
| SC-003 | Ít nhất 95% lần tìm kiếm/danh sách trong bộ demo đã thống nhất hiển thị kết quả trong 3 giây. | Lặp đo trên môi trường demo với tập dữ liệu được ghi lại. |
| SC-004 | 100% bài kiểm thử truy cập trái phép trong bộ test được phê duyệt xác nhận không đọc/sửa/xóa được dữ liệu ngoài quyền. | Báo cáo kiểm thử quyền và truy cập chéo tài khoản. |
| SC-005 | 100% yêu cầu học phần có trạng thái, tiêu chí chấp nhận và bằng chứng cần nộp trong ma trận truy vết trước khi bảo vệ đồ án. | Review ma trận và artifacts cuối kỳ. |
| SC-006 | 100% tổng chi được chọn để trình diễn khớp với phép cộng độc lập trên cùng tập khoản chi hợp lệ. | So sánh kết quả ứng dụng với dữ liệu test chuẩn. |
| SC-007 | Người dùng có thể nhận biết trạng thái loading, empty, success và error trong tất cả màn hình nghiệp vụ được duyệt. | Checklist UI theo từng màn hình/luồng. |
| SC-008 | Không có yêu cầu được báo cáo “Implemented”, “Tested” hoặc “Completed” nếu chưa có bằng chứng tương ứng. | Review cuối kỳ đối chiếu trạng thái và artifacts. |

## 12. Câu hỏi mở, quyết định hoãn và trạng thái kiểm toán

Clarifications phía trên chứa 30 câu hỏi đã được người dùng trả lời. Không hỏi
lại các quyết định đó. Bảng dưới đây chỉ ghi các vấn đề còn mở hoặc cần xử lý
ở giai đoạn khác; “UNRESOLVED” là thiếu chính sách nghiệp vụ, còn “DEFERRED”
là quyết định có thể chốt trong Plan/thiết kế hoặc deliverable học phần.

| ID / Ưu tiên | Vấn đề cần làm rõ hoặc thực hiện | Trạng thái | Căn cứ hiện có / tác động | Giai đoạn phù hợp |
|---|---|---|---|---|
| Q-012 / P2 | Hoàn thành khảo sát website tương tự và lưu nguồn/kết luận. | DEFERRED | Nghĩa vụ học phần FR-018; chưa có bằng chứng khảo sát trong repository. | Plan/tasks học phần; không chặn mô hình nghiệp vụ đã xác nhận. |
| Q-013 / P2 | Cung cấp vị trí và phiên bản ERD/Figma sơ bộ nếu chúng tồn tại ngoài repository. | RESOLVED | ERD hiện có tại `docs/database/ERD.png` cùng `docs/database/database-note.md`; người dùng cung cấp liên kết Figma. Đã đọc ERD và quan sát Page 1/layer names cùng canvas overview; chưa kiểm tra đầy đủ từng frame ở kích thước đọc được. | Đã ghi nhận nguồn và giới hạn kiểm tra trong design review; kiểm tra frame chi tiết là công việc thiết kế tiếp theo, không phải câu hỏi nghiệp vụ. |
| Q-014 / P2 | Chọn AWS services/topology và yêu cầu domain, HTTPS, backup/monitoring. | DEFERRED | Constitution chỉ xác nhận AWS là đích; không chọn dịch vụ hoặc topology. | Plan triển khai/vận hành. |
| Q-015a / P3 | Xác định có yêu cầu xác minh email ngoài luồng khôi phục mật khẩu không. | DEFERRED | Chỉ các sự kiện email khôi phục, thêm thành viên và xóa Plan đã được xác nhận; xác minh email chưa được yêu cầu. | Chốt trước thiết kế/triển khai xác thực nếu cần; không mặc định bật. |
| Q-015b / P3 | Chốt thời hạn, thu hồi và lưu trữ access token. | DEFERRED | Constitution xác nhận hướng access-token nhưng yêu cầu quyết định hành vi cụ thể trước triển khai. | Plan/thiết kế bảo mật và xác thực. |
| Q-016 / P1 | Chi phí cá nhân có thể tồn tại độc lập ngoài Plan không, hay mọi khoản chi phải thuộc một Plan cá nhân/nhóm? | RESOLVED | Người dùng xác nhận mọi khoản chi đều thuộc một Plan; chi phí cá nhân được ghi trong Plan riêng có thể chỉ gồm chủ sở hữu. Không có khoản chi độc lập ngoài Plan. | Đã chốt trong Clarifications; ERD/API/UI phải dùng Plan làm phạm vi khoản chi. |
| Q-017a / P1 | Người thanh toán có bắt buộc là thành viên Plan không? | RESOLVED | Người dùng xác nhận người thanh toán trong Plan nhóm phải là thành viên hiện tại của Plan. | Đã chốt trong Clarifications; áp dụng kiểm tra membership cho dữ liệu khoản chi nhóm. |
| Q-017b / P2 | Trong khoản chi, ngày, ghi chú và địa điểm trường nào bắt buộc? | DEFERRED | Các trường được nêu trong mô hình nghiệp vụ nhưng chưa xác định optionality; đây là chi tiết form/data dictionary. | Chốt trong Plan trước khi tạo form hoặc schema. |
| Q-018a / P1 | Những trường nào trong bài minigame bắt buộc để đăng? | RESOLVED | Người dùng xác nhận tên và luật/hướng dẫn chơi bắt buộc; mô tả, ảnh, số người chơi, thời lượng, dụng cụ và thể loại có thể để trống. | Đã chốt trong Clarifications; dùng để xác định validation và biểu mẫu đăng bài. |
| Q-018b / P1 | Bài minigame được công khai ngay sau khi User đăng hay phải chờ Admin duyệt? | RESOLVED | Người dùng xác nhận bài mới phải chờ Admin duyệt trước khi công khai với Guest. Admin cũng xử lý báo cáo và có thể giữ, ẩn hoặc xóa bài vi phạm. | Đã chốt trong Clarifications; bài mới phải ở trạng thái chờ duyệt và không xuất hiện trong nội dung công khai. |
| Q-018c / P2 | Khi tác giả sửa bài minigame đã được duyệt, bài có phải chờ Admin duyệt lại trước khi nội dung sửa được công khai không? | DEFERRED | Q-018b chốt duyệt trước khi công khai bài mới, nhưng không xác định chu kỳ duyệt cho nội dung sửa. | Chốt trong Plan trước khi triển khai sửa bài. |
| Q-019 / P2 | Ai được sửa/xóa bình luận; báo cáo có lý do/trạng thái nào và Admin xử lý từng báo cáo ra sao? | DEFERRED | Like, comment, report và quyền Admin xem báo cáo/giữ, ẩn hoặc xóa bài đã xác nhận; chi tiết vòng đời comment/report chưa có tiêu chí chấp nhận. | Chốt trong Plan nghiệp vụ trước khi triển khai cộng đồng. |
| Q-020 / P2 | Quy tắc gợi ý minigame theo số thành viên Plan cần xử lý thế nào khi số người phù hợp là một khoảng hoặc không có bài khớp? | DEFERRED | Chức năng gợi ý theo số thành viên đã xác nhận; ranking/empty-state cụ thể là hành vi UX có thể thiết kế sau khi chốt trường minigame. | Plan/thiết kế tính năng minigame. |
| Q-021 / P2 | Chọn nhà cung cấp/API tìm kiếm điểm đến; xác định hoạt động khi thời gian bắt đầu/kết thúc bị bỏ trống và Plan cùng ngày. | DEFERRED | Đã chốt tìm kiếm bên ngoài, lưu metadata có sẵn, manual fallback và trường lịch trình; nhà cung cấp và optionality/ngày hợp lệ chưa chốt. | Plan/thiết kế tích hợp và validation; kiểm thử trước triển khai. |
| Q-022 / P2 | Chốt danh sách thể loại seed và cách xử lý từ khóa tự do. | DEFERRED | Đã xác nhận dùng danh mục thống nhất và cho phép từ khóa tự do; nội dung danh mục có thể dựa trên khảo sát. | Plan/tasks nghiên cứu và thiết kế dữ liệu. |
| Q-023 / P2 | Chốt nơi lưu audit log, thời hạn lưu và quyền đọc log. | DEFERRED | Đã xác nhận trường log và không ghi thao tác xem; chưa chọn giải pháp lưu trữ/vòng đời. | Plan vận hành/bảo mật. |
| Q-024 / P1 | Khi Admin tạo Plan, chủ sở hữu được gán là Admin hay một User khác? | RESOLVED | Người dùng xác nhận Admin trở thành chủ sở hữu của Plan do Admin tạo. | Đã chốt trong Clarifications; `owner_user_id` của Plan mới do Admin tạo tham chiếu Shield User của Admin đó; audit actor vẫn được ghi riêng cho thao tác cần nhật ký. |
## 13. Giả định và ràng buộc

- Đặc tả được viết theo tiếng Việt; thuật ngữ kỹ thuật thông dụng có thể giữ
  tiếng Anh.
- README là bằng chứng hiện có duy nhất cho mục tiêu lập kế hoạch du lịch và
  quản lý chi tiêu cá nhân/nhóm; không suy ra mô hình cộng tác.
- User là chủ sở hữu dữ liệu riêng tư. Chia sẻ cho người khác chỉ có hiệu lực
  sau khi có quy tắc quyền được duyệt.
- Quyền CRUD Plan/khoản chi, quyền Admin và các quy tắc chia chi phí đã được
  người dùng xác nhận. Tiêu chí tìm kiếm Plan là đề xuất cụ thể hóa nghĩa vụ
  học phần; mọi khoản chi thuộc Plan theo quyết định Q-016.
- Email là yêu cầu học phần; đã xác nhận luồng khôi phục mật khẩu, thêm User vào
  Plan và xóa Plan cùng người nhận, nội dung tối thiểu và tối đa 3 lần thử lại
  khi lỗi tạm thời.
- Google/Facebook Login, booking, thanh toán online và marketplace không thuộc
  phạm vi được duyệt.
- ERD hiện có tại `docs/database/ERD.png` và `docs/database/database-note.md`;
  Figma được cung cấp qua liên kết trong `docs/ui-ux/figma-link.md`. Đã kiểm
  tra ERD và inventory/canvas overview của Figma; nội dung từng frame Figma
  chưa được rà soát đầy đủ ở kích thước đọc được.
- Công nghệ và kiến trúc tuân theo Constitution hiện hành; dịch vụ tìm kiếm điểm
  đến đã được xác định là bên ngoài, có phương án nhập thủ công khi lỗi; nhà
  cung cấp/API, AWS services và access-token lifecycle chưa được chọn.
- Dữ liệu demo phải là giả lập; không cần nhập dữ liệu người dùng thực.
- Đặc tả này không tạo schema vật lý, endpoint cụ thể, code, migration, hoặc
  thay đổi giao diện hiện có.

## 14. Tài liệu cần duy trì cho đồ án

Các deliverable dưới đây cần được cập nhật theo đúng trạng thái thực:

1. Tổng quan sản phẩm, vấn đề, người dùng, phạm vi và khảo sát website có nguồn.
2. Role/function matrix và use case descriptions.
3. Sơ đồ kiến trúc, ERD, data dictionary, phân tích 3NF, migrations và dữ liệu
   mẫu sau khi thiết kế được duyệt.
4. UML phù hợp với workflow; không cần mọi loại diagram nếu không giải thích
   hành vi thực.
5. Figma cho màn hình đã duyệt; liên kết mỗi màn hình tới actor và FR.
6. Hợp đồng trao đổi và validation/error behavior cho từng luồng khi bước thiết
   kế kỹ thuật xác định chi tiết.
7. Test plan, test case, kết quả chạy, lỗi còn mở và bằng chứng responsive.
8. Hướng dẫn triển khai, vận hành/bảo trì, health check, bảo vệ cấu hình và
   phương án phục hồi.
9. Báo cáo cuối kỳ phản ánh đúng nội dung đã thiết kế, triển khai, kiểm thử và
   các giới hạn/tính năng tương lai.

## 15. Sẵn sàng cho giai đoạn tiếp theo

Specify đã ghi nhận các quyền và quy tắc cốt lõi đã xác nhận, bao gồm Plan
riêng tư, khoản chi nhóm, cộng đồng minigame và Admin. Có thể bắt đầu
`/speckit-plan` để lập phương án/đầu việc có điều kiện và khảo sát tài liệu;
Q-018c và các quyết định DEFERRED cần được giải quyết ở giai đoạn ghi tại §12,
trước khi khóa phần thiết kế hoặc triển khai tương ứng. Q-016 đã được xác nhận: mọi
khoản chi thuộc Plan, kể cả Plan cá nhân chỉ có chủ sở hữu. Q-017a đã được xác
nhận: người thanh toán khoản chi nhóm phải là thành viên Plan hiện tại. Q-018a
đã được xác nhận: tên và luật/hướng dẫn chơi bắt buộc, các trường minigame còn
lại tùy chọn. Q-018b đã được xác nhận: bài mới chờ Admin duyệt trước khi công
khai. Q-024 đã được xác nhận: Admin là chủ sở hữu Plan do Admin tạo. Q-017b,
Q-018c, Q-019–Q-023 có thể
được phân rã thành quyết định ở Plan/thiết kế, không được tự biến thành yêu
cầu đã duyệt. Chưa có cơ sở nói ERD, Figma hoặc tính năng nghiệp vụ đã hoàn
thành.
