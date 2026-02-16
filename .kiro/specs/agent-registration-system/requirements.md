# Requirements Document

## Introduction

Hệ thống đăng ký đại lý cho phép người dùng đăng ký làm đại lý theo hai hướng khác nhau (người dùng mới và người dùng hiện tại), với quy trình phê duyệt từ admin. Hệ thống tích hợp với cấu trúc PHP MVC hiện tại, sử dụng authentication/authorization có sẵn và PHPMailer để gửi email thông báo.

## Glossary

- **Agent_Registration_System**: Hệ thống đăng ký đại lý chính
- **User**: Người dùng hiện tại hoặc mới của hệ thống
- **Agent**: Đại lý được phê duyệt có quyền truy cập đặc biệt
- **Admin**: Quản trị viên có quyền phê duyệt đăng ký đại lý
- **Registration_Request**: Yêu cầu đăng ký làm đại lý
- **Email_Notification_Service**: Dịch vụ gửi email sử dụng PHPMailer
- **Admin_Panel**: Giao diện quản trị để quản lý users và affiliates

## Requirements

### Requirement 1: Đăng ký đại lý cho người dùng mới

**User Story:** Là một người dùng mới, tôi muốn đăng ký tài khoản và trở thành đại lý cùng lúc, để có thể bắt đầu hoạt động kinh doanh ngay sau khi được phê duyệt.

#### Acceptance Criteria

1. WHEN a new user clicks the "Đại lý" button in nav menu or agent registration CTA, THE Agent_Registration_System SHALL redirect them to the registration page
2. WHEN a new user completes the registration form, THE Agent_Registration_System SHALL display two role options: regular user or agent
3. WHEN a new user selects the agent option, THE Agent_Registration_System SHALL create the account with temporary user access and pending agent status
4. WHEN an agent registration request is submitted by a new user, THE Email_Notification_Service SHALL send a confirmation email stating processing within 24 hours
5. WHEN a new user with pending agent status clicks agent buttons or CTAs, THE Agent_Registration_System SHALL display only a processing notification message

### Requirement 2: Đăng ký đại lý cho người dùng hiện tại

**User Story:** Là một người dùng hiện tại, tôi muốn nâng cấp tài khoản thành đại lý, để có thể mở rộng quyền truy cập và tính năng của mình.

#### Acceptance Criteria

1. WHEN an existing user clicks the "Đại lý" button or agent registration CTA, THE Agent_Registration_System SHALL display an agent registration popup
2. WHEN an existing user submits the agent registration popup, THE Agent_Registration_System SHALL require basic information including a mandatory Gmail address
3. WHEN an existing user completes agent registration, THE Email_Notification_Service SHALL send a confirmation email stating processing within 24 hours
4. WHEN an existing user with pending agent status clicks agent buttons or CTAs, THE Agent_Registration_System SHALL display only a processing notification message

### Requirement 3: Quản lý phê duyệt từ Admin

**User Story:** Là một admin, tôi muốn quản lý và phê duyệt các yêu cầu đăng ký đại lý, để đảm bảo chỉ những người phù hợp mới trở thành đại lý.

#### Acceptance Criteria

1. WHEN an admin views the users tab, THE Admin_Panel SHALL display users with role "người dùng" and status "hoạt động"
2. WHEN an admin views the agents tab, THE Admin_Panel SHALL display agent registration requests with status "chờ duyệt"
3. WHEN an admin changes agent status from "chờ duyệt" to "hoạt động", THE Agent_Registration_System SHALL approve the agent registration
4. WHEN an agent registration is approved, THE Email_Notification_Service SHALL send a success notification to the user
5. WHEN an agent registration is approved, THE Agent_Registration_System SHALL update the user's role to "đại lý" with status "hoạt động" in the users tab

### Requirement 4: Ngăn chặn spam hệ thống

**User Story:** Là một quản trị viên hệ thống, tôi muốn ngăn chặn việc spam yêu cầu đăng ký đại lý, để đảm bảo hệ thống hoạt động ổn định và hiệu quả.

#### Acceptance Criteria

1. WHEN a user has already submitted an agent registration request, THE Agent_Registration_System SHALL prevent duplicate submissions
2. WHEN a user attempts to submit multiple agent registration requests, THE Agent_Registration_System SHALL display the existing request status instead
3. WHEN the system detects potential spam behavior, THE Agent_Registration_System SHALL implement rate limiting for registration requests
4. WHEN a user with pending agent status interacts with agent features, THE Agent_Registration_System SHALL consistently show processing status without allowing new submissions

### Requirement 5: Tích hợp email thông báo

**User Story:** Là một người dùng đã đăng ký làm đại lý, tôi muốn nhận được thông báo email về trạng thái đăng ký, để biết được tiến trình xử lý yêu cầu của mình.

#### Acceptance Criteria

1. WHEN a user submits an agent registration request, THE Email_Notification_Service SHALL send an immediate confirmation email using PHPMailer
2. WHEN an agent registration is approved by admin, THE Email_Notification_Service SHALL send a success notification email
3. WHEN sending notification emails, THE Email_Notification_Service SHALL include relevant information about processing timeframes and next steps
4. WHEN email sending fails, THE Agent_Registration_System SHALL log the error but continue with the registration process

### Requirement 6: Tích hợp với hệ thống hiện tại

**User Story:** Là một developer, tôi muốn hệ thống đăng ký đại lý tích hợp mượt mà với cấu trúc hiện tại, để đảm bảo tính nhất quán và bảo trì dễ dàng.

#### Acceptance Criteria

1. WHEN implementing the agent registration system, THE Agent_Registration_System SHALL utilize the existing PHP MVC architecture
2. WHEN handling user authentication, THE Agent_Registration_System SHALL integrate with the existing authentication and authorization system
3. WHEN managing database operations, THE Agent_Registration_System SHALL use the existing users and affiliates tables
4. WHEN sending emails, THE Agent_Registration_System SHALL utilize the existing PHPMailer configuration
5. WHEN displaying admin interfaces, THE Agent_Registration_System SHALL integrate with the existing admin panel structure