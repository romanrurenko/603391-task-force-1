<?php

namespace frontend\models;


use frontend\models\forms\TasksFilterForm;
use TaskForce\Exception\TaskForceException;
use TaskForce\Helpers\Utils;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;


/**
 * This is the model class for table "task".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $category_id
 * @property int $status_id
 * @property string $address
 * @property float $lat
 * @property float $lng
 * @property int $budget
 * @property string $expire
 * @property string $date_add
 * @property int|null $executor_id
 * @property int $customer_id
 *
 * @property Chat[] $chats
 * @property File[] $files
 * @property Response[] $responses
 * @property Status $status
 * @property Category $category
 * @property User $customer
 * @property User $executor
 */
class Task extends ActiveRecord
{
    public const STATUS_ID_NEW = 1;
    public const STATUS_ID_CANCEL = 2;
    public const STATUS_ID_IN_WORK = 3;
    public const STATUS_ID_COMPLETE = 4;
    public const STATUS_ID_FAILED = 5;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'category_id', 'status_id', 'address', 'lat', 'lng', 'budget', 'expire', 'date_add', 'customer_id'], 'required'],
            [['description'], 'string'],
            [['category_id', 'status_id', 'budget', 'executor_id', 'customer_id'], 'integer'],
            [['lat', 'lng'], 'number'],
            [['expire', 'date_add'], 'safe'],
            [['name'], 'string', 'max' => 128],
            [['address'], 'string', 'max' => 255],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => Status::class, 'targetAttribute' => ['status_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['customer_id' => 'id']],
            [['executor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['executor_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'category_id' => 'Category ID',
            'status_id' => 'Status ID',
            'address' => 'Address',
            'lat' => 'Lat',
            'lng' => 'Lng',
            'budget' => 'Budget',
            'expire' => 'Expire',
            'date_add' => 'Date Add',
            'executor_id' => 'Executor ID',
            'customer_id' => 'Customer ID',
        ];
    }

    /**
     * Gets query for [[Chats]].
     *
     * @return ActiveQuery|ChatQuery
     */
    public function getChats()
    {
        return $this->hasMany(Chat::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return ActiveQuery|FileQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Responses]].
     *
     * @return ActiveQuery|ResponseQuery
     */
    public function getResponses()
    {
        return $this->hasMany(Response::class, ['task_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return ActiveQuery|StatusQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return ActiveQuery|CategoryQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(User::class, ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[Executor]].
     *
     * @return ActiveQuery|UserQuery
     */
    public function getExecutor()
    {
        return $this->hasOne(User::class, ['id' => 'executor_id']);
    }

    /**
     * {@inheritdoc}
     * @return TaskQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TaskQuery(get_called_class());
    }


    /**
     * Возвращает массив задач со статусом 'Новый' и без привязки к адресу
     * @param array $request
     * @return Query
     * @throws TaskForceException
     */
    public static function findNewTask(array $request = []): Query
    {
        $query = new Query();
        $list = [];
        if (isset($request['CategoriesFilterForm']['categories'])) {
            foreach ($request['CategoriesFilterForm']['categories'] as $key => $item) {
                if ($item) {
                    $list[] = sprintf("'%s'", $key);
                }
            }
        }

        $query->select(['t.*', 'c.name as cat_name', 'c.icon as icon'])->from('task t')
            ->join('LEFT JOIN', 'category as c', 't.category_id = c.id')
            ->where('t.status_id = 1');


        // todo добавить задания из города пользователя, либо из города, выбранного пользователем в текущей сессии.


        if (!empty($list)) {
            $categoryList = sprintf('c.id in (%s)', implode(",", $list));
            $query->andWhere($categoryList);
        }

        if (strlen($request['TasksFilterForm']['searchName']) > 0) {
            $query->andWhere(['LIKE','t.name',  $request['TasksFilterForm']['searchName'], false]);
        }

        if ($request['TasksFilterForm']['withoutExecutor']) {
            $query->andWhere('t.executor_id IS NULL');
        }

        if ($request['TasksFilterForm']['remoteWork']) {
            $query->andWhere('t.lat IS NULL AND t.lng IS NULL');
        }

        if (isset($request['TasksFilterForm']['timeInterval'])) {
            $datetime = TasksFilterForm::timeBeforeInterval($request['TasksFilterForm']['timeInterval']);
            $query->andWhere("t.date_add > STR_TO_DATE('$datetime','%Y-%m-%d %H:%i:%s')");
        }

        return $query->orderBy(['date_add' => SORT_DESC]);

    }


    /**
     * Найти задчу по ID
     * @param int $id
     * @return array
     * @throws TaskForceException
     */
    public static function findTaskByID(int $id = null): ?array
    {
        $query = new Query();

        $query->select(['t.*', 'c.name as cat_name', 'c.icon as icon'])->from('task t')
            ->join('LEFT JOIN', 'category as c', 't.category_id = c.id')
            ->where(['t.id' => $id])
            ->limit(1);

        $model = $query->one();

        if (!empty($model)) {
            $model['afterTime'] = Utils::getTimeAfter((string)$model['date_add']);
        } else {
            $model = null;
        };

        return $model;
    }

    /**
     * @param int $id
     * @return int|null
     * @throws TaskForceException
     */
    public static function findCountTasksByUserId(int $id): ?int
    {

        if (empty($id) && ($id < 1)) {
            throw new TaskForceException('Не задан ID пользователя');
        }

        return self::find()->where(['id' => $id])->andWhere(['status_id' => self::STATUS_ID_COMPLETE])->count();

    }


}
