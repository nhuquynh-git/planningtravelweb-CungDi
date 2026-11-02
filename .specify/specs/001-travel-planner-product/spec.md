# Đặc tả sản phẩm: Cùng Đi – Travel Planner

**Feature Directory**: `.specify/specs/001-travel-planner-product`

**Feature Branch**: Chưa tạo (không có hook tạo branch)

**Ngày tạo**: 2026-11-02

**Trạng thái**: Draft — cần xác nhận phạm vi nghiệp vụ trước khi lập kế hoạch kỹ thuật

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
- Xây dựng trải nghiệm nhất quán cho Guest, User và Admin mà không tự suy diễn
  quyền Admin hoặc chức năng chia sẻ.
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

## 2. Người dùng và phạm vi quyền

| Vai trò | Mục đích | Phạm vi đã xác nhận | Quyền/chức năng chưa được suy diễn |
|---|---|---|---|
| Guest | Khách chưa xác thực. | Có thể tiếp cận luồng công khai và đăng ký/đăng nhập nếu được xác nhận ở quy trình sản phẩm. | Không được xem dữ liệu riêng tư hoặc thực hiện thao tác được bảo vệ. Phạm vi thông tin du lịch công khai chưa quyết định. |
| User | Người dùng thông thường đã xác thực. | Hướng sản phẩm có quản lý kế hoạch và chi tiêu cá nhân/nhóm. | Không mặc định có thể cộng tác, chia sẻ dữ liệu, xem dữ liệu User khác hoặc thanh toán/đối soát. |
| Admin | Quản trị viên hệ thống. | Vai trò Admin được xác nhận. | Chức năng quản trị, phạm vi dữ liệu và quyền xem/sửa/xóa đều chưa quyết định; không mặc định quyền truy cập toàn bộ dữ liệu. |

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

Việc Fetch API hiện diện trong repository không tự chứng minh yêu cầu jQuery
AJAX đã được đáp ứng. Nhóm phải kiểm tra lại yêu cầu cụ thể của giảng viên trước
khi kết luận tuân thủ.

### 3.2. Yêu cầu sản phẩm đã xác nhận

| ID | Yêu cầu/định hướng | Căn cứ |
|---|---|---|
| PRD-001 | Sản phẩm thuộc lĩnh vực lập kế hoạch du lịch. | README và tên/mô tả dự án. |
| PRD-002 | Sản phẩm hướng đến quản lý chi tiêu cá nhân và nhóm. | README dự án. |
| PRD-003 | Các vai trò được xác lập là Guest, User, Admin. | Project Constitution và yêu cầu người dùng. |
| PRD-004 | Quyết định công nghệ/kiến trúc đã thống nhất phải được giữ nguyên trừ khi có phê duyệt thay đổi. | Project Constitution. |

Các yêu cầu này xác nhận mục tiêu và ranh giới, không tự xác nhận mọi thao tác
CRUD, chia sẻ nhóm, lịch trình, báo cáo, hoặc quyền Admin.

### 3.3. Đề xuất luồng sản phẩm phiên bản đầu

Các luồng sau là đề xuất cụ thể hóa mục tiêu; nhóm cần duyệt trước khi coi là
phạm vi thực thi:

| Mã | Đề xuất | Giá trị | Trạng thái |
|---|---|---|---|
| PROP-001 | User tạo, xem, cập nhật, xóa kế hoạch của mình; kế hoạch có tên, điểm đến và khoảng ngày. | Cung cấp luồng lập kế hoạch có thể trình diễn và kiểm thử. | Đề xuất; cần xác nhận trường dữ liệu, nhiều điểm đến và chính sách xóa. |
| PROP-002 | User ghi nhận khoản chi cá nhân hoặc khoản chi thuộc chuyến đi nhóm; xem tổng các khoản đã ghi. | Gắn yêu cầu chi tiêu cá nhân/nhóm với mục đích sản phẩm. | Đề xuất; chưa bao gồm phân bổ nợ, hoàn tiền, chia tỷ lệ hay mời thành viên. |
| PROP-003 | User tìm kiếm kế hoạch của mình theo tên/điểm đến và xem danh sách/chi tiết. | Ánh xạ yêu cầu tìm kiếm/hiển thị vào dữ liệu sản phẩm. | Đề xuất; cần xác nhận tiêu chí và bộ lọc. |
| PROP-004 | User xem tổng chi phí theo kế hoạch và bản tóm tắt có thể in. | Ánh xạ thống kê/in ấn vào quy trình hữu ích. | Đề xuất; cần xác nhận nội dung báo cáo và quy tắc tiền tệ. |
| PROP-005 | Chỉ bổ sung lịch trình hoạt động theo ngày nếu nhóm xác nhận đây là phần bắt buộc của trải nghiệm lập kế hoạch. | Tránh tự tạo phạm vi lịch trình khi chưa có bằng chứng. | Chưa quyết định. |

### 3.4. Ngoài phạm vi hiện tại và định hướng tương lai

- Đăng nhập Google/Facebook: hướng tương lai, chưa được phê duyệt/triển khai.
- Đặt chỗ thương mại, thanh toán trực tuyến, tích hợp booking bên ngoài: ngoài
  phạm vi cho đến khi có yêu cầu riêng; không cần thiết để đáp ứng mô tả hiện có.
- Mạng xã hội, bảng tin, đánh giá công khai, chat, mời cộng tác viên và chia sẻ
  kế hoạch công khai: chưa được xác nhận.
- Chia tiền, quản lý công nợ, hoàn ứng, thanh toán giữa các thành viên: không
  suy ra chỉ từ yêu cầu ghi nhận chi tiêu nhóm.
- Quản lý toàn bộ nội dung hoặc người dùng bởi Admin: chưa được xác nhận.

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

**Ghi chú phạm vi**: Hình thức xác thực, vòng đời token, lưu trữ thông tin xác
thực, khôi phục mật khẩu và đăng xuất cần được chốt trong thiết kế phù hợp; đặc
tả không chọn phương án lưu token.

### User Story 2 — Quản lý kế hoạch du lịch cá nhân (Ưu tiên: P1, đề xuất)

Là User, tôi muốn tạo và quản lý kế hoạch của mình để tập hợp thông tin cho một
chuyến đi tại một nơi.

**Giá trị**: Cụ thể hóa mục tiêu lập kế hoạch du lịch mà không thêm booking hoặc
dịch vụ thương mại.

**Kiểm thử độc lập**: Tạo kế hoạch có dữ liệu hợp lệ, xem lại, cập nhật, tìm
kiếm và xóa kế hoạch; tài khoản khác không đọc hoặc sửa được kế hoạch riêng tư.

**Tiêu chí chấp nhận**:

1. **Given** User đã xác thực, **When** tạo kế hoạch với các trường bắt buộc hợp
   lệ, **Then** kế hoạch được lưu và xuất hiện trong danh sách của chính User.
2. **Given** ngày kết thúc sớm hơn ngày bắt đầu, **When** lưu kế hoạch, **Then**
   hệ thống từ chối dữ liệu và chỉ rõ trường cần sửa.
3. **Given** User mở kế hoạch của mình, **When** cập nhật thông tin hợp lệ,
   **Then** nội dung mới được hiển thị khi xem lại.
4. **Given** User yêu cầu xóa kế hoạch, **When** xác nhận thao tác, **Then**
   kế hoạch không còn xuất hiện trong danh sách/tìm kiếm của User; chính sách
   đối với khoản chi liên quan phải tuân theo quyết định xóa đã được phê duyệt.
5. **Given** User khác biết mã/đường dẫn tài nguyên riêng tư, **When** yêu cầu
   xem hoặc sửa tài nguyên đó, **Then** hệ thống từ chối mà không tiết lộ dữ
   liệu.

**Trường dữ liệu tạm đề xuất**: tên kế hoạch, điểm đến dạng văn bản, ngày bắt
đầu, ngày kết thúc, mô tả tùy chọn. Đây chưa phải ERD hoặc danh sách trường đã
phê duyệt.

### User Story 3 — Ghi nhận chi tiêu chuyến đi (Ưu tiên: P1, đề xuất)

Là User, tôi muốn ghi nhận khoản chi cá nhân hoặc chi phí gắn với chuyến đi
nhóm để theo dõi số tiền đã ghi nhận.

**Giá trị**: Ánh xạ trực tiếp định hướng quản lý chi tiêu cá nhân/nhóm vào một
quy trình có thể kiểm thử.

**Kiểm thử độc lập**: Ghi khoản chi hợp lệ, xem tổng, sửa và xóa khoản chi; tài
nguyên không được truy cập bởi User khác nếu chưa có quy tắc chia sẻ được duyệt.

**Tiêu chí chấp nhận**:

1. **Given** User có kế hoạch được phép truy cập, **When** ghi khoản chi với số
   tiền dương và thông tin bắt buộc, **Then** khoản chi xuất hiện trong đúng
   ngữ cảnh kế hoạch.
2. **Given** số tiền thiếu, không phải số hợp lệ hoặc không dương, **When** lưu
   khoản chi, **Then** hệ thống từ chối và thông báo lỗi phù hợp.
3. **Given** khoản chi được cập nhật hoặc xóa thành công, **When** xem lại danh
   sách/tổng, **Then** số liệu phản ánh dữ liệu còn hiệu lực.
4. **Given** User không sở hữu khoản chi và không có quyền được cấp, **When**
   yêu cầu đọc/sửa/xóa, **Then** hệ thống từ chối.
5. **Given** hai cách phân loại cá nhân/nhóm được nhóm phê duyệt, **When** xem
   tổng, **Then** tổng cá nhân và tổng nhóm được phân biệt theo cùng quy tắc
   tiền tệ đã công bố.

**Ghi chú phạm vi**: Không có thanh toán hoặc chia nợ. Người tham gia, người
thanh toán, cách phân bổ và tiền tệ chưa được chốt.

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
   in chỉ bao gồm dữ liệu được phép xem và có thể đọc được trên giấy.

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

Chưa tạo User Story nghiệp vụ cho Admin vì chưa có quyền hoặc chức năng quản trị
được xác nhận. Trước khi bổ sung trang hoặc thao tác Admin, nhóm phải xác định
đối tượng quản lý, quyền xem/thêm/sửa/xóa, audit cần thiết và tiêu chí chấp
nhận. Mặc định từ chối mọi thao tác quản trị chưa được phê duyệt.

## 5. Yêu cầu

### 5.1. Yêu cầu chức năng

Các yêu cầu có trạng thái **Đề xuất** không được xem là phạm vi được duyệt chỉ
vì chúng có mã FR; cần xác nhận qua Open Questions trước khi lập kế hoạch.

| ID | Yêu cầu chức năng | Trạng thái |
|---|---|---|
| FR-001 | Sản phẩm MUST hỗ trợ mục tiêu lập kế hoạch du lịch và quản lý chi tiêu cá nhân/nhóm. | Đã xác nhận |
| FR-002 | Hệ thống MUST phân biệt Guest, User và Admin; MUST không thêm vai trò khác nếu chưa có phê duyệt. | Đã xác nhận |
| FR-003 | Hệ thống MUST bảo vệ dữ liệu riêng tư bằng xác thực, kiểm tra quyền và kiểm tra quyền sở hữu ở mọi thao tác liên quan. | Đã xác nhận theo Constitution |
| FR-004 | Guest MUST có thể tiếp cận nội dung công khai và luồng đăng ký/đăng nhập; nội dung công khai cụ thể cần phê duyệt. | Đề xuất |
| FR-005 | User MUST có thể tạo, xem, cập nhật và xóa kế hoạch thuộc quyền sở hữu của mình. | Đề xuất |
| FR-006 | Kế hoạch MUST từ chối khoảng ngày không hợp lệ; các trường bắt buộc phải được nhóm xác định trước khi duyệt form. | Đề xuất |
| FR-007 | User MUST có thể ghi, xem, cập nhật và xóa khoản chi trong phạm vi được phép. | Đề xuất |
| FR-008 | Hệ thống MUST phân biệt chi phí cá nhân và chi phí nhóm nếu nhóm xác nhận mô hình này; không tự thực hiện chia nợ/thanh toán. | Chưa quyết định |
| FR-009 | Hệ thống MUST tính tổng từ các khoản chi hợp lệ còn hiệu lực và làm rõ cách xử lý tiền tệ/làm tròn. | Đề xuất |
| FR-010 | User MUST có thể tìm kiếm kế hoạch và xem kết quả/chi tiết chỉ trong phạm vi được phép. | Đề xuất |
| FR-011 | Hệ thống MUST hiển thị trạng thái rỗng, tải, thành công và lỗi phù hợp tại các luồng sản phẩm. | Đã xác nhận theo Constitution |
| FR-012 | User MUST có thể tạo bản in tóm tắt kế hoạch/chi phí sau khi nội dung báo cáo được phê duyệt. | Nghĩa vụ học phần; nội dung đề xuất |
| FR-013 | Hệ thống MUST cung cấp thống kê gắn với quy trình sản phẩm; tổng chi theo kế hoạch là phương án đề xuất để đáp ứng học phần. | Nghĩa vụ học phần; phương án cần duyệt |
| FR-014 | Hệ thống MUST hỗ trợ gửi email cho một sự kiện nghiệp vụ được duyệt; không gửi email thử nghiệm/vô nghĩa chỉ để đánh dấu hoàn thành học phần. | Nghĩa vụ học phần; use case chưa quyết định |
| FR-015 | Hệ thống MUST từ chối truy cập không xác thực hoặc không đủ quyền bằng kết quả dễ hiểu và không làm lộ tài nguyên riêng tư. | Đã xác nhận theo Constitution |
| FR-016 | Hệ thống MUST xử lý dữ liệu trùng lặp, tài nguyên không tồn tại và lỗi lưu trữ mà không báo thành công giả. | Đã xác nhận theo Constitution |
| FR-017 | Hệ thống MUST cung cấp quy trình xóa có xác nhận cho dữ liệu quan trọng; tác động lên dữ liệu liên quan cần quy tắc được duyệt. | Đề xuất |
| FR-018 | Hệ thống MUST ghi nhận nghiên cứu website tương tự và liên kết kết luận nghiên cứu với quyết định sản phẩm có căn cứ. | Nghĩa vụ học phần |
| FR-019 | Hệ thống MUST cung cấp phân tích vai trò/chức năng, ERD, lập luận 3NF và dữ liệu mẫu phù hợp với yêu cầu đã duyệt. | Nghĩa vụ học phần |
| FR-020 | Hệ thống MUST có UML phù hợp với luồng thực tế và giao diện Figma truy vết đến yêu cầu; không tạo sơ đồ/màn hình trang trí. | Nghĩa vụ học phần |
| FR-021 | Hệ thống MUST có bằng chứng kiểm thử, triển khai, vận hành, tài liệu kỹ thuật và báo cáo phản ánh đúng trạng thái thật. | Nghĩa vụ học phần |
| FR-022 | Các hợp đồng trao đổi, trường dữ liệu và quy tắc chi tiết MUST được chốt sau khi xác nhận quy trình; đặc tả này không ấn định danh sách giao tiếp hoặc lược đồ vật lý. | Ràng buộc phạm vi |

### 5.2. Quy tắc nghiệp vụ và kiểm tra dữ liệu

- Ngày kết thúc chuyến đi không được sớm hơn ngày bắt đầu; ngày bắt đầu/kết thúc
  có thể bằng nhau hay không cần nhóm xác nhận.
- Số tiền khoản chi phải là giá trị số dương; độ chính xác, tiền tệ và xử lý
  khoản chi hoàn/âm cần được chốt trước khi thiết kế dữ liệu.
- Tổng chi là tổng của các khoản được xác định là còn hiệu lực; không được cộng
  lặp khi cập nhật, tải lại hoặc hiển thị nhiều trang kết quả.
- User không được truy cập dữ liệu riêng của người khác chỉ bằng cách thay đổi
  định danh tài nguyên.
- Xóa kế hoạch có dữ liệu liên quan phải tuân thủ chính sách lưu giữ/xóa đã
  phê duyệt; không tự suy ra xóa dây chuyền hoặc lưu trữ mềm.
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
| Dữ liệu trùng | Áp dụng quy tắc uniqueness được duyệt; báo rõ xung đột, không ghi đè âm thầm. |
| Chưa xác thực | Từ chối thao tác riêng tư và yêu cầu xác thực mà không tiết lộ dữ liệu. |
| Không có quyền sở hữu/quyền chia sẻ | Từ chối xem/sửa/xóa; không tiết lộ sự tồn tại hoặc nội dung nhạy cảm quá mức. |
| Tài nguyên không tồn tại | Trả trạng thái không tìm thấy phù hợp; không hiển thị dữ liệu cũ như còn tồn tại. |
| Kết quả tìm kiếm rỗng | Hiển thị trạng thái rỗng, không phải lỗi hệ thống. |
| Mất mạng hoặc lỗi lưu trữ | Thông báo thao tác chưa hoàn tất; cho phép thử lại an toàn; tránh gửi trùng khi người dùng thử lại. |
| Xung đột cập nhật đồng thời | Không ghi đè âm thầm thay đổi mới hơn; báo người dùng tải lại hoặc xác nhận giải pháp được duyệt. |
| Lỗi gửi email | Không báo gửi thành công; lưu/hiển thị trạng thái phù hợp và cho phép xử lý lại theo chính sách được duyệt. |
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
| Kế hoạch du lịch | Gom thông tin một chuyến đi của User. | PRD-001, PROP-001, FR-005 | Được ngụ ý mạnh; thuộc tính, vòng đời và quan hệ chưa duyệt. |
| Khoản chi | Ghi nhận chi tiêu cá nhân hoặc gắn với chuyến đi. | PRD-002, PROP-002, FR-007–009 | Được ngụ ý mạnh; quy tắc phân loại/tiền tệ chưa duyệt. |
| Điểm đến | Có thể là chuỗi mô tả trong kế hoạch hoặc dữ liệu danh mục có quản lý. | PROP-001, PROP-003 | Chưa quyết định; không tạo danh mục nếu không có use case. |
| Người tham gia chuyến đi | Chỉ cần nếu có thành viên, cộng tác hoặc phân bổ chi phí. | Câu hỏi về nhóm | Đề xuất; chưa được xác nhận. |
| Mục lịch trình | Chỉ cần nếu lịch trình theo ngày/thứ tự là yêu cầu sản phẩm. | PROP-005 | Chưa quyết định. |

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
| Kế hoạch du lịch | Tập hợp thông tin chuyến đi. | Tạo/xem/sửa/xóa, tìm kiếm, in. | FR-005, FR-006, FR-010, FR-012 | Có thể liên kết một-nhiều với khoản chi; cần xác nhận xóa. |
| Khoản chi | Số liệu chi phí được ghi nhận. | Thêm/sửa/xóa, tính tổng. | FR-007–009, FR-012–014 | Thuộc User; liên kết với kế hoạch nếu nghiệp vụ duyệt. |
| Điểm đến | Hỗ trợ mô tả/tìm kiếm nơi đến. | Ghi kế hoạch, tìm kiếm. | PROP-001, PROP-003 | Chưa xác định là thuộc tính kế hoạch hay danh mục riêng. |
| Thành viên/lịch trình | Hỗ trợ cộng tác hoặc lịch hoạt động nếu duyệt. | Chia sẻ kế hoạch/lập lịch. | PROP-005 và câu hỏi mở | Chưa có quan hệ được duyệt; không thêm vào ERD hiện tại. |

### 6.5. Workflow-to-Data Mapping

| Workflow | Actor | Dữ liệu cần | Khái niệm liên quan | Kiểm tra | Kết quả mong muốn |
|---|---|---|---|---|---|
| Tạo và cập nhật kế hoạch | User | Chủ sở hữu, tên, điểm đến, ngày, mô tả tùy chọn | Tài khoản, kế hoạch, có thể điểm đến | Quyền sở hữu; trường bắt buộc; thứ tự ngày | User thấy phiên bản mới nhất của kế hoạch mình. |
| Ghi khoản chi | User | Chủ sở hữu, kế hoạch liên quan, số tiền, ngày, nội dung, phân loại | Tài khoản, kế hoạch, khoản chi | Quyền; số tiền dương; tiền tệ; ngày | Khoản chi thuộc đúng phạm vi và được tính một lần. |
| Tìm kiếm/tổng hợp/in | User | Tiêu chí tìm, dữ liệu được phép, khoản chi hiệu lực | Kế hoạch, khoản chi | Phạm vi quyền; tiêu chí; quy tắc tổng | Kết quả, tổng và nội dung in nhất quán. |
| Quản trị hệ thống | Admin | Chưa xác định | Chưa xác định | Chưa có chính sách quyền | Không triển khai trước khi phê duyệt use case. |

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
| Chi tiết/chỉnh sửa kế hoạch | User | Thông tin kế hoạch và form | Xem, sửa, xác nhận xóa | Kiểm tra dữ liệu và giữ nội dung nhập hợp lệ khi có lỗi | FR-005, FR-006, FR-017 |
| Khoản chi và tổng hợp | User | Danh sách khoản chi, tổng | Thêm, sửa, xóa, lọc cá nhân/nhóm | Tổng phản ánh dữ liệu hiện tại theo quy tắc được duyệt | FR-007–009, FR-013 |
| Bản in | User | Tóm tắt kế hoạch/chi phí | Xem trước, in | Chỉ in dữ liệu hợp lệ, đúng quyền và đọc được | FR-012 |
| Khu vực Admin | Admin | Chưa xác định | Chưa xác định | Không tạo màn hình trước khi chức năng/quyền được duyệt | Câu hỏi mở Admin |

### 7.4. Design Gap Analysis

| Vấn đề | Artifact | Hiện trạng | Cải thiện đề xuất | Lý do | Ưu tiên |
|---|---|---|---|---|---|
| Không có ERD nghiệp vụ trong repository | ERD/database | Chỉ có bảng kiểm tra kết nối | Thu thập ERD hiện có nếu lưu ngoài repo; đối chiếu với FR; sau đó duyệt mô hình | Tránh khẳng định sai schema và tránh tạo bảng theo giả định | P1 |
| Không có Figma hoặc màn hình nghiệp vụ để kiểm tra | UI/UX | Chỉ có màn hình trạng thái hệ thống | Xác định nguồn Figma; kiểm kê trang/section và gắn với FR trước khi tinh chỉnh | Chưa thể giữ layout nếu layout chưa có trong artifacts được cung cấp | P1 |
| Chưa có luồng nghiệp vụ sản phẩm trong ứng dụng | Product UI | UI hiện tại chỉ kiểm tra sức khỏe dịch vụ | Duyệt luồng User Story 1–4 rồi mới thiết kế màn hình | Đảm bảo giao diện giải quyết nhu cầu thay vì thêm trang tùy ý | P1 |
| Phân quyền Admin chưa có yêu cầu nghiệp vụ | Role model | Có cấu hình nhóm nhưng chưa có hành vi Admin được xác nhận | Xác định tài nguyên, thao tác, ranh giới và audit trước khi tạo trang Admin | Tránh quyền quá rộng | P1 |
| Chưa xác định tương tác nhóm | Expense/group flow | Mô tả có chi tiêu nhóm, không có mô hình thành viên/chia tiền | Chốt ai có thể xem/ghi và cách tính trước khi thiết kế cộng tác | Tác động lớn đến quyền và ERD | P1 |

## 8. Bảng đối chiếu giữa workflow, UI và dữ liệu

### 8.1. Điểm chưa khớp cần giải quyết

- Tìm kiếm, thống kê, in và email là yêu cầu học phần nhưng hiện chưa có
  workflow sản phẩm/endpoint nghiệp vụ tương ứng trong repository.
- Mô tả chi tiêu nhóm chưa xác định thành viên, quyền ghi khoản chi, cách phân
  bổ, quyền xem và tiền tệ.
- Màn hình tạo/sửa/xóa kế hoạch và khoản chi chưa có thiết kế hoặc triển khai
  trong repository.
- Chưa xác định dữ liệu điểm đến là text, danh mục quản trị hay nguồn khác.
- Chưa xác định có itinerary/scheduling hay chỉ lưu thông tin chuyến đi và chi
  phí.
- Chưa có use case nghiệp vụ cho Admin.
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
| JavaScript và AJAX/jQuery | FR-021 | §3.1, §12 | Giảng viên xác nhận cách đáp ứng; không coi Fetch API tự động thay thế jQuery nếu môn yêu cầu | Xác nhận đề cương/giảng viên, demo luồng bất đồng bộ | Not started — cần xác nhận yêu cầu giảng viên |
| PHP, MySQL, CodeIgniter, MVC, client-server | FR-021 | Constitution, §3.1 | Có luồng nghiệp vụ thực tế qua kiến trúc đã thống nhất | Source, sơ đồ kiến trúc, test luồng tích hợp | Specified — một phần cấu hình/scaffold; chưa có nghiệp vụ |
| Thêm/sửa/xóa và hiển thị dữ liệu | FR-005, FR-007 | §4, §5.1 | CRUD kế hoạch/khoản chi được duyệt, có validation và quyền | Demo, test và tài liệu chức năng | Not started |
| Tìm kiếm | FR-010 | §4, §5.1 | Kết quả đúng phạm vi quyền; xử lý rỗng | Test truy vấn và demo | Not started |
| Thống kê | FR-009, FR-013 | §4, §5.1 | Tổng khớp các khoản hợp lệ theo quy tắc đã duyệt | Test tính tổng và demo | Not started |
| In ấn | FR-012 | §4, §5.1 | Bản in đọc được, khớp dữ liệu và quyền | Mẫu in và kiểm thử | Not started |
| Gửi email | FR-014 | §5.1, §12 | Có sự kiện nhận email và hành vi lỗi được duyệt | Test gửi thành công/thất bại và cấu hình vận hành | Not started — use case chưa xác định |
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

## 12. Câu hỏi mở và đăng ký làm rõ

Các câu hỏi này phải được giải quyết trước khi khóa plan/ERD hoặc triển khai
những phần bị ảnh hưởng. Ưu tiên P1 ảnh hưởng trực tiếp phạm vi, quyền hoặc dữ
liệu.

| ID / Ưu tiên | Câu hỏi cần xác nhận | Vì sao quan trọng | Phần bị ảnh hưởng / hệ quả |
|---|---|---|---|
| Q-001 / P1 | Luồng lập kế hoạch tối thiểu gồm thông tin chuyến đi nào? Một kế hoạch có một hay nhiều điểm đến; có bắt buộc itinerary theo ngày không? | Quyết định workflow cốt lõi và ranh giới giữa plan/destination/itinerary. | PROP-001, PROP-005, FR-005/006; ERD, UI, UML và API. |
| Q-002 / P1 | “Chi tiêu nhóm” nghĩa là ghi tổng chi chung, hay phải quản lý thành viên, người trả, phần mỗi người, công nợ/hoàn ứng? | Các diễn giải có độ phức tạp, quyền và mô hình dữ liệu rất khác nhau. | PROP-002, FR-007–009; quyền chia sẻ, ERD, thống kê và kiểm thử. |
| Q-003 / P1 | Kế hoạch/chi phí là riêng tư hay chia sẻ? Nếu chia sẻ, ai mời thành viên và thành viên được đọc/ghi/xóa gì? | Tác động bảo mật và quyền sở hữu. | Guest/User/Admin, FR-003/015; entity membership, màn hình và API. |
| Q-004 / P1 | User được xóa kế hoạch thế nào khi có khoản chi; xóa dây chuyền, lưu trữ hay chặn xóa? | Quyết định toàn vẹn dữ liệu và khả năng khôi phục. | FR-005/017; chính sách FK, migration và UX xác nhận. |
| Q-005 / P1 | Admin cần quản lý dữ liệu nào, được đọc/sửa/xóa gì, có cần nhật ký thao tác không? | Vai trò Admin đã xác nhận nhưng chưa có use case nghiệp vụ. | §2, FR-002; quyền, giao diện Admin, audit và test. |
| Q-006 / P1 | Đề cương/giảng viên có yêu cầu bắt buộc jQuery AJAX hay chấp nhận Fetch API? | Không thể kết luận tuân thủ môn nếu thiếu yêu cầu chính thức. | Ma trận học phần, stack constraint và báo cáo. |
| Q-007 / P1 | Email được gửi cho sự kiện sản phẩm nào, tới ai, và nội dung/thất bại được xử lý thế nào? | Cần use case có ý nghĩa; tránh email giả tạo chỉ để đáp ứng checklist. | FR-014, bảo mật dữ liệu, cấu hình vận hành và test. |
| Q-008 / P2 | Điểm đến là văn bản do User nhập hay danh mục có quản lý? Có cần tìm kiếm địa điểm công khai không? | Quyết định có cần entity/dữ liệu danh mục và chức năng Admin hay không. | PROP-001/003; ERD, form và khả năng tìm kiếm. |
| Q-009 / P2 | Thống kê bắt buộc là tổng theo chuyến đi, cá nhân/nhóm, theo thời gian hay loại chi? | “Thống kê” trong yêu cầu học phần chưa chỉ rõ đầu ra. | FR-009/013, UI, ERD và tiêu chí test. |
| Q-010 / P2 | Bản in gồm kế hoạch, lịch trình, danh sách chi hay tổng hợp nào; có cần định dạng tải xuống không? | Xác định giá trị người dùng và phạm vi thực hiện phần in. | FR-012, UI và test. |
| Q-011 / P2 | Quy tắc tiền tệ là VND cố định hay hỗ trợ nhiều tiền tệ; cách làm tròn/tỷ giá nếu có? | Ảnh hưởng tính đúng tổng và thiết kế dữ liệu. | FR-008/009, báo cáo và test. |
| Q-012 / P2 | Nghiên cứu website tương tự hiện ở đâu; nhóm đã có nguồn/phân tích chưa? | Repository chưa có bằng chứng để trích dẫn hoặc kết luận. | FR-018, UI và báo cáo; tránh bịa nghiên cứu. |
| Q-013 / P2 | ERD/Figma hiện có được lưu ngoài repository ở đâu để review đầy đủ? | Người dùng nói có thiết kế sơ bộ nhưng repository không có artifact tương ứng. | §6–§8; đánh giá và chính sách giữ layout. |
| Q-014 / P2 | AWS cần dùng dịch vụ/topology nào; triển khai có yêu cầu domain, HTTPS, backup/monitoring cụ thể không? | AWS mới là đích định hướng, chưa có quyết định vận hành. | Deployment, NFR, secrets, recovery và học phần. |
| Q-015 / P3 | Chức năng khôi phục mật khẩu, xác minh email, và lifecycle access token nào cần cho bản đầu? | Ảnh hưởng luồng xác thực, nhưng có thể chốt riêng nếu không đổi phạm vi cốt lõi. | User Story 1, bảo mật, UI và test. |

## 13. Giả định và ràng buộc

- Đặc tả được viết theo tiếng Việt; thuật ngữ kỹ thuật thông dụng có thể giữ
  tiếng Anh.
- README là bằng chứng hiện có duy nhất cho mục tiêu lập kế hoạch du lịch và
  quản lý chi tiêu cá nhân/nhóm; không suy ra mô hình cộng tác.
- User là chủ sở hữu dữ liệu riêng tư. Chia sẻ cho người khác chỉ có hiệu lực
  sau khi có quy tắc quyền được duyệt.
- CRUD kế hoạch/khoản chi, tìm kiếm, tổng chi và bản in là đề xuất có giá trị
  để ánh xạ yêu cầu môn học vào use case, nhưng cần nhóm phê duyệt.
- Email là yêu cầu học phần; sự kiện nghiệp vụ cụ thể chưa xác định.
- Google/Facebook Login, booking, thanh toán online và marketplace không thuộc
  phạm vi được duyệt.
- Figma và ERD có thể tồn tại ngoài repository; cho đến khi được cung cấp,
  trạng thái trong đặc tả chỉ phản ánh những file có thể kiểm tra tại đây.
- Công nghệ và kiến trúc tuân theo Constitution hiện hành; AWS services và
  access-token lifecycle không được giả định trong tài liệu này.
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

Tài liệu đủ để bắt đầu `/speckit-clarify`, nhưng chưa đủ căn cứ khóa ERD hoặc
phân rã implementation. Trước `/speckit-plan`, ưu tiên giải quyết Q-001 đến
Q-007, xác định nguồn Figma/ERD nếu có, và phê duyệt phạm vi MVP.
