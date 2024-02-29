# NFT 마켓 솔루션 백엔드 (토큰형)

## 프로젝트 설명
    1. 시작하기 이전에
    2. 필수 설치
    3. 필수 PHP 모듈
    4. env 파일 설정
    5. config 파일 설정
    6. 자동 이메일 HTML 변경
    7. 라이센스


### 1.시작하기 이전에
````

스마트 계약이 실행하는 모든 계약실행에 대해서 콜백으로 로그를 남기며 통계 기록 또는 사용자가 NFT 발행 트랜잭션 생성후
실수로 브라우저를 닫았을경우 콜백에서 체크하여 발행됨으로 전환시키기 위해서 사용합니다
발행수,판매성사 금액,경매성사 금액,판매성사횟수,경매성사횟수 등을 DB에 저장합니다

NFT 최대 업로드 용량은 기본 8메가바이트 로 설정되어 있습니다.
php.ini 에 용량 설정을 꼭! 해주시길 바랍니다

````

### 2.필수 설치
````
Apache , PHP7.4 , IPFS , MYSQL 5.8

IPFS 서버가 정상적으로 작동하고있지 않다면 NFT를 업로드할수 없습니다.
모든 메타데이타,이미지,동영상 은 IPFS에 저장됩니다.
````

### 3.필수 PHP 모듈
````
php-mbstring , php-gd , php-mysql , php-xml , php-curl , php-gmp , php-zlib
````

### 4.env 파일 설정
````
DB 설정과 관련하여 변경사항이 있을경우 .env 파일내 아래 내용들을 변경하시거나.
config/database.php 파일을 변경 해주길 바랍니다.

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD


````

### 5.config 파일 설정

````
config/cors.php

allowed_origins // CORS 를 허용할 웹사이트 주소
````

````
config/ipfs.php

ipfs['base_url'] // IPFS 서버 호스트 주소
ipfs['port'] // IPFS 서버 포트
````

````
config/database.php

MYSQL 연결설정 필수
````

### 6.자동 이메일 HTML 변경
````
자동이메일이 사용되는곳
-인증된 저자 신청 승인 및 거부
-문의 답변

자동 이메일은 AWS 의 SES 서비스를 사용하여 발송합니다.
AWS에 설정된 리전과 동일한 리전에 SES 가 정상적으로 활성화가 되어있어야 정상적인 작동을 합니다.

HTML 코드 변경 경로
resources/views/emails/contact-reply.blade.php

````

````
