import { IsString, IsNotEmpty, IsNumber, IsPositive, MinLength } from "class-validator";

export class CreateChangeOrderDto {
  @IsString() @MinLength(3) title: string;
  @IsString() @MinLength(10) description: string;
  @IsNumber() @IsPositive() amount: number;
}
