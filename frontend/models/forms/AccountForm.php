<?php

namespace frontend\models\forms;

use frontend\models\City;
use frontend\models\Profile;
use frontend\models\User;
use frontend\models\Work;
use TaskForce\Exception\TaskForceException;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

class AccountForm extends Model
{
    public const NOT_CORRECT_CITY = 'Не допустимый город';
    const LONG_NAME_NOTIFY = 'Наименование должно быть более 30 символов.';

    public $avatarFile = null;
    public $file = null;
    public string $avatar = '';
    public string $name = '';
    public string $email = '';
    public ?int $cityId = null;
    public ?string $birthday = '';
    public ?string $info = '';

    public string $newPassword = '';
    public string $repeatPassword = '';
    public string $phone;
    public string $skype;
    public string $telegram = '';
    public array $notifications = [];
    public bool $showMyContact = false;
    public bool $dontShowProfile = false;


    public function init(): void
    {
        $userId = Yii::$app->user->identity->getId();
        $profile = Profile::findByUserId($userId);
        $this->name = Yii::$app->user->identity->name ?? '';
        $this->email = $profile['email'] ?? '';
        $this->cityId = Yii::$app->user->identity->city_id;
        $this->birthday = $profile['birthday'] ?? '';
        $this->info = $profile['about'] ?? '';
        $this->skype = $profile['skype'] ?? '';
        $this->telegram = $profile['messenger'] ?? '';
        $this->phone = $profile['phone'] ?? '';
        $this->avatar = $profile['avatar'] ?? '';
        $this->showMyContact = (bool)$profile['show_it'];
        $this->dontShowProfile = (bool)$profile['show_only_executor'];
    }

    public function attributeLabels(): array
    {
        return [
            'avatarFile' => 'Сменить аватар',
            'avatar' => 'Аватар',
            'name' => 'Ваше имя',
            'email' => 'Email',
            'cityId' => 'Город',
            'birthday' => 'День рождения',
            'info' => 'Информация о себе',
            'specialisations' => 'Категории',
            'newPassword' => 'Новый пароль',
            'repeatPassword' => 'Повторить пароль',
            'phone' => 'Телефон',
            'skype' => 'Skype',
            'telegram' => 'Telegram',
            'file' => 'Фотографии работ',
            'showMyContact' => 'Показывать мои контакты только заказчику',
            'dontShowProfile' => 'Не показывать мой профиль',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required', 'message' => 'Поле не заполнено.'],
            [['name', 'email', 'info'], 'trim'],
            [['name', 'info'], 'string'],
            ['name', 'string', 'min' => 2, 'tooShort' => 'Имя должно быть не менее 2 символов.'],
            ['name', 'string', 'max' => 128, 'tooLong' => 'Имя должно быть не более 255 символов.'],
            ['email', 'email', 'message' => 'Не корректный email'],
            ['cityId', 'integer'],
            ['newPassword', 'compare', 'compareAttribute' => 'repeatPassword'],
            [
                'cityId',
                'exist',
                'targetClass' => City::class,
                'targetAttribute' => 'id',
                'message' => self::NOT_CORRECT_CITY
            ],
            [
                'birthday',
                'date',
                'format' => 'php:Y-m-d',
            ],
            ['avatarFile', 'file', 'extensions' => ['jpg', 'jpeg', 'gif', 'png']],
            [['showMyContact', 'dontShowProfile'], 'boolean'],
            [
                'phone',
                'match',
                'pattern' => '/^[0-9]{11}$/',
                'message' => 'Введите телефон в формате 89231231212'
            ],
            ['info', 'string', 'min' => 30, 'tooShort' => self::LONG_NAME_NOTIFY],
            [
                ['telegram', 'skype'],
                'string',
                'max' => 255,
                'tooShort' => self::LONG_NAME_NOTIFY
            ],
            ['info', 'string', 'min' => 30, 'tooShort' => self::LONG_NAME_NOTIFY],
            ['avatarFile', 'safe'],
            ['birthday', 'date', 'format' => 'yyyy-mm-dd'],
            [
                ['newPassword', 'repeatPassword'],
                'string',
                'min' => 8,
                'tooShort' => 'Пароль должен быть не менее 8 символов.'
            ],
        ];
    }

    /**
     * data validator
     * @param $attribute
     */
    public function isDateInFuture($attribute): void
    {
        $isPastDate = strtotime('now') > strtotime($this->$attribute);
        if ($isPastDate) {
            $this->addError($attribute, 'Дата не может быть меньше текущей.');
        }
    }

    /**
     * @param array $files
     * @return bool|null
     * @throws Exception
     */
    public function uploadWorkPhotos(array $files): ?bool
    {
        $userId = Yii::$app->user->identity->getId();
        $path = Yii::getAlias('@frontend/web/uploads/works/') . $userId;
        FileHelper::createDirectory($path);
        foreach ($files as $file) {
            $newFilename = substr(md5(microtime() . rand(0, 9999)), 0, 20) . '.' . $file->extension;
            $file->saveAs($path . '/' . $newFilename);
            $workFileName = new Work();
            $workFileName->filename = $file->name;
            $workFileName->generated_name = $newFilename;
            $workFileName->user_id = $userId;
            $workFileName->save();
        }

        return true;
    }

    /**
     * @param array $file
     * @param int $profileId
     * @return bool
     * @throws NotFoundHttpException
     */
    public function uploadAvatar(array $file, int $profileId): bool
    {
        $userId = Yii::$app->user->identity->getId();
        $fileName = $userId . '.' . $file[0]->extension;
        $uploadPath = Yii::getAlias('@frontend') . '/web/uploads/avatars/';
        $file[0]->saveAs($uploadPath . '/' . $fileName);

        $profile = Profile::findOrFail($profileId);
        $profile->avatar = $fileName;
        $profile->save();

        return true;
    }

    /**
     * @return int|null
     * @throws NotFoundHttpException|Exception
     * @throws TaskForceException
     */
    public function saveData(): ?int
    {
        $userId = Yii::$app->user->identity->getId();
        $profileId = (int)Profile::findByUserId($userId)['profile_id'];

        if ($this->avatarFile) {
            $this->uploadAvatar($this->avatarFile, $profileId);
        }

        $profile = Profile::findOrFail($profileId);
        $user = User::findOrFail($userId);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $profile->about = $this->info;
            $profile->phone = $this->phone;
            $profile->skype = $this->skype;
            $profile->messenger = $this->telegram;
            $profile->show_it = (bool)$this->showMyContact;
            $profile->show_only_executor = (bool)$this->dontShowProfile;
            $profile->birthday = $this->birthday;
            $profile->update();

            $user->city_id = $this->cityId;
            if ($this->newPassword !== '') {
                $user->password = Yii::$app->getSecurity()->generatePasswordHash($this->newPassword);
            }
            $user->email = $this->email;
            $user->update();


            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new TaskForceException("Ошибка обновления данных. " . $e->getMessage());
        }

        return true;
    }


}